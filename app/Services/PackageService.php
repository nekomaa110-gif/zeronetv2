<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageAttribute;
use App\Models\RadCheck;
use App\Models\RadGroupCheck;
use App\Models\RadGroupReply;
use App\Models\RadReply;
use App\Models\RadUserGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PackageService
{
    // Atribut ini dikelola eksklusif oleh VoucherService — tidak boleh ada di paket
    public const TIME_BLOCKED = ['Expiration', 'Max-All-Session', 'Auth-Type'];

    public const ATTRIBUTE_PRESETS = [
        'Mikrotik-Group',
        'Mikrotik-Rate-Limit',
        'Mikrotik-Recv-Limit',
        'Mikrotik-Xmit-Limit',
        'Mikrotik-Total-Limit',
        'Session-Timeout',
        'Idle-Timeout',
        'Simultaneous-Use',
        'Framed-IP-Address',
        'WISPr-Bandwidth-Max-Up',
        'WISPr-Bandwidth-Max-Down',
    ];

    public const OPERATORS = [':=', '=', '+=', '=='];

    // Hanya group tables — radcheck/radreply per-user dikelola oleh RadiusUserService & VoucherService
    public const TARGET_TABLES = [
        'radgroupreply',
        'radgroupcheck',
    ];

    public function all(): Collection
    {
        // Sumber utama: UNION dari radius tables (logika panel lama)
        $radiusGroupNames = collect(DB::select('
            SELECT groupname FROM radgroupcheck
            UNION
            SELECT groupname FROM radgroupreply
            ORDER BY groupname
        '))->pluck('groupname');

        // Index packages yang sudah terdaftar di panel baru
        $packages = Package::withCount('attributes')
            ->get()
            ->keyBy('groupname');

        return $radiusGroupNames->map(function (string $groupname) use ($packages) {
            $pkg = $packages->get($groupname);

            $userCount = RadUserGroup::where('groupname', $groupname)->count();

            if ($pkg) {
                // Profil yang sudah dikelola panel baru — data penuh
                return [
                    'id'              => $pkg->id,
                    'groupname'       => $pkg->groupname,
                    'description'     => $pkg->description,
                    'is_active'       => $pkg->is_active,
                    'attribute_count' => $pkg->attributes_count,
                    'user_count'      => $userCount,
                    'is_legacy'       => false,
                ];
            }

            // Profil dari panel lama — derive status dari Auth-Type di radgroupcheck
            $authType   = RadGroupCheck::where('groupname', $groupname)
                ->where('attribute', 'Auth-Type')
                ->value('value');
            $attrCount  = RadGroupCheck::where('groupname', $groupname)->count()
                        + RadGroupReply::where('groupname', $groupname)->count();

            return [
                'id'              => null,
                'groupname'       => $groupname,
                'description'     => null,
                'is_active'       => $authType === 'Accept',
                'attribute_count' => $attrCount,
                'user_count'      => $userCount,
                'is_legacy'       => true,
            ];
        })->values();
    }

    public function find(Package $package): Package
    {
        return $package->loadMissing('attributes');
    }

    public function create(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            $package = Package::create([
                'groupname'   => $data['groupname'],
                'description' => $data['description'] ?? null,
                'is_active'   => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            $this->saveAttributes($package, $data['attributes'] ?? []);
            $this->syncToRadius($package);

            return $package;
        });
    }

    public function update(Package $package, array $data): void
    {
        DB::transaction(function () use ($package, $data) {
            $package->update([
                'description' => $data['description'] ?? null,
                'is_active'   => isset($data['is_active']) ? (bool) $data['is_active'] : false,
            ]);

            $package->attributes()->delete();
            $this->saveAttributes($package, $data['attributes'] ?? []);
            $this->syncToRadius($package);
        });
    }

    public function toggle(Package $package): void
    {
        DB::transaction(function () use ($package) {
            $newActive = ! $package->is_active;
            $package->update(['is_active' => $newActive]);

            RadGroupCheck::where('groupname', $package->groupname)
                ->where('attribute', 'Auth-Type')
                ->update(['value' => $newActive ? 'Accept' : 'Reject']);
        });
    }

    public function delete(Package $package): void
    {
        DB::transaction(function () use ($package) {
            $this->clearFromRadius($package->groupname);
            $package->delete();
        });
    }

    public function exists(string $groupname): bool
    {
        return Package::where('groupname', $groupname)->exists();
    }

    public function existsInRadius(string $groupname): bool
    {
        return RadGroupCheck::where('groupname', $groupname)->exists()
            || RadGroupReply::where('groupname', $groupname)->exists();
    }

    /**
     * Import profil lama (dari radius tables) ke tabel packages.
     * Radius tables TIDAK disentuh — tetap utuh sampai user klik Simpan.
     */
    public function importFromRadius(string $groupname): Package
    {
        return DB::transaction(function () use ($groupname) {
            $authType = RadGroupCheck::where('groupname', $groupname)
                ->where('attribute', 'Auth-Type')
                ->value('value');

            // Tidak ada Auth-Type = anggap aktif (default panel lama selalu buat Auth-Type Accept)
            $isActive = ($authType === null) || ($authType === 'Accept');

            $package = Package::create([
                'groupname'   => $groupname,
                'description' => null,
                'is_active'   => $isActive,
            ]);

            $sortOrder = 0;

            // Import dari radgroupcheck (kecuali Auth-Type — dikelola lewat is_active)
            RadGroupCheck::where('groupname', $groupname)
                ->where('attribute', '!=', 'Auth-Type')
                ->get()
                ->each(function ($row) use ($package, &$sortOrder) {
                    if (in_array($row->attribute, self::TIME_BLOCKED, true)) {
                        return;
                    }
                    $package->attributes()->create([
                        'attribute'    => $row->attribute,
                        'op'           => $row->op,
                        'value'        => $row->value,
                        'target_table' => 'radgroupcheck',
                        'sort_order'   => $sortOrder++,
                    ]);
                });

            // Import dari radgroupreply
            RadGroupReply::where('groupname', $groupname)
                ->get()
                ->each(function ($row) use ($package, &$sortOrder) {
                    if (in_array($row->attribute, self::TIME_BLOCKED, true)) {
                        return;
                    }
                    $package->attributes()->create([
                        'attribute'    => $row->attribute,
                        'op'           => $row->op,
                        'value'        => $row->value,
                        'target_table' => 'radgroupreply',
                        'sort_order'   => $sortOrder++,
                    ]);
                });

            return $package;
        });
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function saveAttributes(Package $package, array $attributes): void
    {
        foreach ($attributes as $i => $attr) {
            if (empty($attr['attribute'])) {
                continue;
            }
            // Atribut waktu dikelola oleh VoucherService — tidak boleh ada di paket
            if (in_array($attr['attribute'], self::TIME_BLOCKED, true)) {
                continue;
            }
            $package->attributes()->create([
                'attribute'    => trim($attr['attribute']),
                'op'           => $attr['op'] ?? ':=',
                'value'        => trim($attr['value'] ?? ''),
                'target_table' => $attr['target_table'] ?? 'radgroupreply',
                'sort_order'   => (int) $i,
            ]);
        }
    }

    private function syncToRadius(Package $package): void
    {
        $groupname = $package->groupname;

        $this->clearFromRadius($groupname);

        RadGroupCheck::create([
            'groupname' => $groupname,
            'attribute' => 'Auth-Type',
            'op'        => ':=',
            'value'     => $package->is_active ? 'Accept' : 'Reject',
        ]);

        foreach ($package->attributes as $attr) {
            match ($attr->target_table) {
                'radgroupreply' => RadGroupReply::create([
                    'groupname' => $groupname,
                    'attribute' => $attr->attribute,
                    'op'        => $attr->op,
                    'value'     => $attr->value,
                ]),
                'radgroupcheck' => RadGroupCheck::create([
                    'groupname' => $groupname,
                    'attribute' => $attr->attribute,
                    'op'        => $attr->op,
                    'value'     => $attr->value,
                ]),
                default => null,
            };
        }
    }

    private function clearFromRadius(string $groupname): void
    {
        RadGroupCheck::where('groupname', $groupname)->delete();
        RadGroupReply::where('groupname', $groupname)->delete();
        RadCheck::where('username', $groupname)->delete();
        RadReply::where('username', $groupname)->delete();
    }
}
