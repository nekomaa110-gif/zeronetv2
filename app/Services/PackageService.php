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
        return Package::withCount('attributes')
            ->orderBy('groupname')
            ->get()
            ->map(fn(Package $pkg) => [
                'id'              => $pkg->id,
                'groupname'       => $pkg->groupname,
                'description'     => $pkg->description,
                'is_active'       => $pkg->is_active,
                'attribute_count' => $pkg->attributes_count,
                'user_count'      => RadUserGroup::where('groupname', $pkg->groupname)->count(),
            ]);
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
