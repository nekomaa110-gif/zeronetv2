{{-- Partial: tabel + pagination user hotspot. Dipakai oleh full view dan response AJAX. --}}

@php
    /** Avatar palette — deterministic by username for stable per-user color. */
    $avPalette = ['#2D6BFF', '#7C3AED', '#10B981', '#F59E0B', '#EC4899', '#0EA5E9', '#EF4444', '#14B8A6'];

    /** Color the profile/group badge by name (stable). */
    $groupPalette = [
        ['bg' => 'bg-blue-50',    'tx' => 'text-blue-700',    'dot' => 'bg-blue-500',    'dk_bg' => 'dark:bg-blue-500/15',    'dk_tx' => 'dark:text-blue-300'],
        ['bg' => 'bg-violet-50',  'tx' => 'text-violet-700',  'dot' => 'bg-violet-500',  'dk_bg' => 'dark:bg-violet-500/15',  'dk_tx' => 'dark:text-violet-300'],
        ['bg' => 'bg-emerald-50', 'tx' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'dk_bg' => 'dark:bg-emerald-500/15', 'dk_tx' => 'dark:text-emerald-300'],
        ['bg' => 'bg-amber-50',   'tx' => 'text-amber-700',   'dot' => 'bg-amber-500',   'dk_bg' => 'dark:bg-amber-500/15',   'dk_tx' => 'dark:text-amber-300'],
        ['bg' => 'bg-pink-50',    'tx' => 'text-pink-700',    'dot' => 'bg-pink-500',    'dk_bg' => 'dark:bg-pink-500/15',    'dk_tx' => 'dark:text-pink-300'],
        ['bg' => 'bg-sky-50',     'tx' => 'text-sky-700',     'dot' => 'bg-sky-500',     'dk_bg' => 'dark:bg-sky-500/15',     'dk_tx' => 'dark:text-sky-300'],
    ];
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-left text-[13.5px]">
        <thead class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-100 dark:border-gray-700">
            <tr class="text-[11.5px] uppercase tracking-[0.06em] font-medium text-gray-500 dark:text-gray-400">
                <th class="px-5 py-3 w-12">#</th>
                <th class="px-5 py-3">
                    <span class="inline-flex items-center gap-1">Username
                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </th>
                <th class="px-5 py-3">Profil / Paket</th>
                <th class="px-5 py-3">Expire</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
            @forelse($users as $user)
                @php
                    $expired = $user['expiry'] !== '-' && \Carbon\Carbon::parse($user['expiry'])->isPast();
                    $isDisabled = ! $user['active'];
                    $isExpired  = $user['active'] && $expired;
                    $isActive   = $user['active'] && ! $expired;

                    // Avatar bg
                    $avHash = crc32($user['username']);
                    $avColor = $avPalette[$avHash % count($avPalette)];
                    $initials = collect(preg_split('/[\s_.\-]+/', $user['username']))
                        ->filter()->take(2)
                        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                        ->implode('');
                    if ($initials === '') $initials = mb_strtoupper(mb_substr($user['username'], 0, 2));

                    // Group badge
                    $gHasValue = $user['group'] !== '-' && $user['group'] !== '';
                    $gp = $gHasValue ? $groupPalette[crc32($user['group']) % count($groupPalette)] : null;

                    // Sisa waktu (relative)
                    $sisa = null; $sisaCls = '';
                    if ($user['expiry'] !== '-') {
                        $exp = \Carbon\Carbon::parse($user['expiry']);
                        $diffDays = now()->diffInDays($exp, false);
                        if ($diffDays < 0) {
                            $sisa = abs(round($diffDays)) . ' hr lalu';
                            $sisaCls = 'text-red-500 dark:text-red-400';
                        } elseif ($diffDays <= 3) {
                            $sisa = round($diffDays) . ' hr lagi';
                            $sisaCls = 'text-amber-500 dark:text-amber-400';
                        } elseif ($diffDays <= 14) {
                            $sisa = round($diffDays) . ' hr lagi';
                            $sisaCls = 'text-emerald-500 dark:text-emerald-400';
                        } else {
                            $sisa = round($diffDays) . ' hr lagi';
                            $sisaCls = 'text-gray-500 dark:text-gray-400';
                        }
                    }
                @endphp
                <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-5 py-3.5 align-middle text-xs text-gray-400 dark:text-gray-500 tabular-nums">
                        {{ $users->firstItem() + $loop->index }}
                    </td>

                    {{-- Username + avatar --}}
                    <td class="px-5 py-3.5 align-middle">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full grid place-items-center text-[11.5px] font-semibold text-white tracking-wider shrink-0"
                                 style="background: linear-gradient(135deg, {{ $avColor }} 0%, {{ $avColor }}cc 100%); box-shadow: 0 2px 6px {{ $avColor }}30;">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 dark:text-white truncate">{{ $user['username'] }}</div>
                                <div class="text-[11.5px] font-mono text-gray-400 dark:text-gray-500 truncate">@ {{ $user['username'] }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Group / profile --}}
                    <td class="px-5 py-3.5 align-middle">
                        @if ($gHasValue)
                            <span class="inline-flex items-center gap-1.5 h-6 px-2.5 rounded-full text-xs font-medium border border-gray-200 dark:border-gray-700 {{ $gp['bg'] }} {{ $gp['tx'] }} {{ $gp['dk_bg'] }} {{ $gp['dk_tx'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $gp['dot'] }}"></span>
                                {{ $user['group'] }}
                            </span>
                        @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                        @endif
                    </td>

                    {{-- Expire --}}
                    <td class="px-5 py-3.5 align-middle">
                        @if ($user['expiry'] !== '-')
                            <div class="leading-tight">
                                <div class="text-[13px] font-medium text-gray-900 dark:text-white">{{ $user['expiry'] }}</div>
                                @if ($sisa)
                                    <div class="text-[11.5px] mt-0.5 {{ $sisaCls }}">{{ $sisa }}</div>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                        @endif
                    </td>

                    {{-- Status pill --}}
                    <td class="px-5 py-3.5 align-middle">
                        @if ($isDisabled)
                            <span class="inline-flex items-center gap-1.5 h-[22px] px-2.5 rounded-full text-[11.5px] font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                Nonaktif
                            </span>
                        @elseif ($isExpired)
                            <span class="inline-flex items-center gap-1.5 h-[22px] px-2.5 rounded-full text-[11.5px] font-medium bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                Expired
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 h-[22px] px-2.5 rounded-full text-[11.5px] font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                                <span class="relative w-1.5 h-1.5 rounded-full bg-current">
                                    <span class="absolute inset-0 rounded-full bg-current opacity-40 animate-ping"></span>
                                </span>
                                Aktif
                            </span>
                        @endif
                    </td>

                    {{-- Row actions --}}
                    <td class="px-5 py-3.5 align-middle text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1 opacity-0 translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-150">
                            {{-- Edit --}}
                            <a href="{{ route('user-hotspot.edit', $user['username']) }}" title="Edit"
                               class="w-7 h-7 grid place-items-center rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Toggle aktif/nonaktif --}}
                            <form method="POST" action="{{ route('user-hotspot.toggle', $user['username']) }}" class="inline-flex">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="{{ $user['active'] ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        class="w-7 h-7 grid place-items-center rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-colors
                                               {{ $user['active']
                                                    ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10 hover:border-amber-300'
                                                    : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 hover:border-emerald-300' }}">
                                    @if ($user['active'])
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/>
                                        </svg>
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>

                            {{-- Hapus --}}
                            <form method="POST" action="{{ route('user-hotspot.destroy', $user['username']) }}" class="inline-flex"
                                  onsubmit="return confirm('Hapus user {{ addslashes($user['username']) }}? Tindakan ini tidak bisa dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus"
                                        class="w-7 h-7 grid place-items-center rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 hover:border-red-300 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="inline-flex flex-col items-center gap-2.5 text-gray-500 dark:text-gray-400">
                            <div class="w-10 h-10 rounded-xl grid place-items-center bg-gray-100 dark:bg-gray-700/60 text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                @if ($search || $group || $status)
                                    Tidak ada user yang cocok
                                @else
                                    Belum ada user hotspot
                                @endif
                            </div>
                            <div class="text-[12.5px]">
                                @if ($search || $group || $status)
                                    Coba ubah pencarian atau filter
                                @else
                                    <a href="{{ route('user-hotspot.create') }}" class="text-brand-600 hover:underline">Tambah sekarang →</a>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->total() > 0)
    <div class="flex items-center justify-between gap-3 px-5 py-3 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 text-[12.5px] text-gray-500 dark:text-gray-400">
        <span>
            Menampilkan
            <strong class="text-gray-900 dark:text-white tabular-nums">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</strong>
            dari
            <strong class="text-gray-900 dark:text-white tabular-nums">{{ $users->total() }}</strong>
            user
        </span>
        @if ($users->hasPages())
            <div class="[&_nav]:!m-0 [&_nav]:flex [&_nav]:items-center [&_nav]:gap-1">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endif
