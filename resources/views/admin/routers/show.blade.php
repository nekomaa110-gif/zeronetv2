@extends('admin.layouts.app')

@section('title', $routerName)
@section('page-title', $routerName)

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $routerName }}</h2>
            <p class="text-sm text-gray-400 font-mono mt-0.5">{{ $routerHost }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.routers.index') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            @if(auth()->user()->role === 'admin')
            <form method="POST" action="{{ route('admin.routers.reboot', $routerId) }}"
                  onsubmit="return confirm('Yakin reboot {{ $routerName }}? Semua koneksi aktif akan terputus sementara.')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reboot
                </button>
            </form>
            <a href="{{ route('admin.routers.backup', $routerId) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Backup Config
            </a>
            @endif
        </div>
    </div>

    {{-- ── Router Stats ─────────────────────────────────────────────────── --}}
    <div x-data="routerStats('{{ route('admin.routers.stats', $routerId) }}')"
         x-init="load()"
         x-cloak
         class="mb-6">

        {{-- Loading skeleton --}}
        <div x-show="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 h-28 animate-pulse"></div>
            @endfor
        </div>

        {{-- Offline / error --}}
        <div x-show="!loading && !online"
             class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-5 py-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-700 dark:text-red-400">Router tidak dapat dijangkau</p>
                <p class="text-xs text-red-500 dark:text-red-500 mt-0.5" x-text="error"></p>
            </div>
            <button @click="load()" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 font-medium underline whitespace-nowrap">Coba lagi</button>
        </div>

        {{-- ✅ 4-column stat grid --}}
        <div x-show="!loading && online" class="grid grid-cols-2 md:grid-cols-4 gap-4">

            {{-- CPU --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">CPU Load</p>
                <p class="text-3xl font-bold mb-3 tabular-nums"
                   :class="stats.cpu_load > 80 ? 'text-red-500' : stats.cpu_load > 50 ? 'text-yellow-500' : 'text-green-500'"
                   x-text="stats.cpu_load + '%'"></p>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all duration-500"
                         :class="stats.cpu_load > 80 ? 'bg-red-500' : stats.cpu_load > 50 ? 'bg-yellow-500' : 'bg-green-500'"
                         :style="'width:' + stats.cpu_load + '%'"></div>
                </div>
            </div>

            {{-- RAM --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">RAM</p>
                <p class="text-3xl font-bold mb-3 tabular-nums"
                   :class="stats.mem_pct > 85 ? 'text-red-500' : stats.mem_pct > 60 ? 'text-yellow-500' : 'text-blue-500'"
                   x-text="stats.mem_pct + '%'"></p>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all duration-500"
                         :class="stats.mem_pct > 85 ? 'bg-red-500' : stats.mem_pct > 60 ? 'bg-yellow-500' : 'bg-blue-500'"
                         :style="'width:' + stats.mem_pct + '%'"></div>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2" x-text="fmtBytes(stats.used_mem) + ' / ' + fmtBytes(stats.total_mem)"></p>
            </div>

            {{-- Storage --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">Storage</p>
                <p class="text-3xl font-bold mb-3 tabular-nums"
                   :class="stats.hdd_pct > 85 ? 'text-red-500' : stats.hdd_pct > 60 ? 'text-yellow-500' : 'text-purple-500'"
                   x-text="stats.hdd_pct + '%'"></p>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all duration-500"
                         :class="stats.hdd_pct > 85 ? 'bg-red-500' : stats.hdd_pct > 60 ? 'bg-yellow-500' : 'bg-purple-500'"
                         :style="'width:' + stats.hdd_pct + '%'"></div>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2" x-text="fmtBytes(stats.used_hdd) + ' / ' + fmtBytes(stats.total_hdd)"></p>
            </div>

            {{-- Uptime --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">Uptime</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mb-1 leading-tight" x-text="stats.uptime"></p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium" x-text="stats.identity"></p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="stats.board + ' · ROS ' + stats.version"></p>
            </div>

        </div>
    </div>

    {{-- ── Active Hotspot Users ─────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-data="hotspotUsers(
             '{{ route('admin.routers.hotspot-users', $routerId) }}',
             '{{ route('admin.routers.disconnect',    $routerId) }}'
         )"
         x-init="init()"
         x-cloak>

        {{-- Section header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">User Hotspot Aktif</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    <span x-show="!loading" x-text="users.length + ' user online'"></span>
                    <span x-show="loading" class="animate-pulse">Memuat...</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Countdown badge --}}
                <span x-show="!loading"
                      class="text-xs text-gray-400 dark:text-gray-500 tabular-nums hidden sm:inline">
                    Refresh dalam <span class="font-medium text-gray-600 dark:text-gray-300" x-text="countdown + 's'"></span>
                </span>
                {{-- Refresh button --}}
                <button @click="load()" :disabled="loading"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors shadow-sm disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Loading skeleton rows --}}
        <div x-show="loading" class="divide-y divide-gray-100 dark:divide-gray-700/50">
            @for ($i = 0; $i < 6; $i++)
            <div class="flex items-center gap-4 px-5 py-3.5">
                <div class="h-3 w-28 bg-gray-100 dark:bg-gray-700 rounded-full animate-pulse"></div>
                <div class="h-3 w-24 bg-gray-100 dark:bg-gray-700 rounded-full animate-pulse"></div>
                <div class="h-3 w-36 bg-gray-100 dark:bg-gray-700 rounded-full animate-pulse hidden md:block"></div>
                <div class="h-3 w-20 bg-gray-100 dark:bg-gray-700 rounded-full animate-pulse hidden lg:block ml-auto"></div>
            </div>
            @endfor
        </div>

        {{-- Fetch error --}}
        <div x-show="!loading && fetchError" class="px-5 py-10 text-center">
            <svg class="w-8 h-8 text-red-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm font-medium text-red-500 dark:text-red-400" x-text="fetchError"></p>
            <button @click="load()" class="mt-2 text-xs text-brand-600 dark:text-brand-400 underline">Coba lagi</button>
        </div>

        {{-- ✅ User table --}}
        <div x-show="!loading && !fetchError" class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap">User</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap">IP</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap table-cell">MAC</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap hidden lg:table-cell">Host</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap">Uptime</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap hidden lg:table-cell">Sisa Waktu</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap table-cell">Rx / Tx</th>
                        @if(auth()->user()->role === 'admin')
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">

                    {{-- Empty state --}}
                    <template x-if="users.length === 0">
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada user hotspot aktif</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="user in users" :key="user['.id']">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">

                            {{-- User --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-brand-600 dark:text-brand-400"
                                              x-text="(user.user || '?')[0].toUpperCase()"></span>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white text-sm" x-text="user.user || '-'"></span>
                                </div>
                            </td>

                            {{-- IP --}}
                            <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-300 whitespace-nowrap" x-text="user.address || '-'"></td>

                            {{-- MAC --}}
                            <td class="px-4 py-3 font-mono text-xs text-gray-400 dark:text-gray-500 table-cell whitespace-nowrap" x-text="user['mac-address'] || '-'"></td>

                            {{-- Host --}}
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 hidden lg:table-cell whitespace-nowrap" x-text="user['host-name'] || '-'"></td>

                            {{-- Uptime --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400"
                                      x-text="user.uptime || '-'"></span>
                            </td>

                            {{-- Sisa waktu --}}
                            <td class="px-4 py-3 text-xs hidden lg:table-cell whitespace-nowrap">
                                <span x-show="user['session-time-left'] && user['session-time-left'] !== '0s'"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400"
                                      x-text="user['session-time-left']"></span>
                                <span x-show="!user['session-time-left'] || user['session-time-left'] === '0s'"
                                      class="text-gray-300 dark:text-gray-600">∞</span>
                            </td>

                            {{-- Rx / Tx --}}
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 table-cell whitespace-nowrap">
                                <span class="text-green-600 dark:text-green-400" x-text="fmtBytes(parseInt(user['bytes-in'] ?? 0))"></span>
                                <span class="text-gray-300 dark:text-gray-600 mx-1">/</span>
                                <span class="text-blue-600 dark:text-blue-400" x-text="fmtBytes(parseInt(user['bytes-out'] ?? 0))"></span>
                            </td>

                            {{-- Aksi --}}
                            @if(auth()->user()->role === 'admin')
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button @click="disconnect(user['.id'], user.user)"
                                    :disabled="disconnecting === user['.id']"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium border transition-colors disabled:opacity-40
                                           text-red-600 dark:text-red-400 border-red-200 dark:border-red-800/60
                                           hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <svg x-show="disconnecting !== user['.id']" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    <svg x-show="disconnecting === user['.id']" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Putus
                                </button>
                            </td>
                            @endif
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Footer: last updated --}}
        <div x-show="!loading && !fetchError" class="px-5 py-2.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/20">
            <p class="text-xs text-gray-400 dark:text-gray-500">
                Terakhir diperbarui: <span x-text="lastUpdated"></span>
                &nbsp;·&nbsp; Auto-refresh setiap 30 detik
            </p>
        </div>

    </div>

@endsection

@push('scripts')
<script>
function routerStats(statsUrl) {
    return {
        statsUrl,
        loading: true,
        online: false,
        stats: {},
        error: '',
        async load() {
            this.loading = true;
            try {
                const res  = await fetch(this.statsUrl);
                const data = await res.json();
                this.online = data.online;
                this.stats  = data.stats ?? {};
                this.error  = data.error ?? '';
            } catch (e) {
                this.online = false;
                this.error  = e.message;
            } finally {
                this.loading = false;
            }
        },
        fmtBytes(n) {
            n = parseInt(n) || 0;
            if (n >= 1073741824) return (n / 1073741824).toFixed(1) + ' GB';
            if (n >= 1048576)    return (n / 1048576).toFixed(1) + ' MB';
            if (n >= 1024)       return (n / 1024).toFixed(1) + ' KB';
            return n + ' B';
        },
    };
}

function hotspotUsers(usersUrl, disconnectUrl) {
    return {
        usersUrl,
        disconnectUrl,
        loading: true,
        users: [],
        fetchError: '',
        disconnecting: null,
        countdown: 30,
        lastUpdated: '—',
        _timer: null,
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,

        init() {
            this.load();
        },

        async load() {
            this.loading = true;
            this.fetchError = '';
            try {
                const res  = await fetch(this.usersUrl);
                const data = await res.json();
                if (data.success) {
                    this.users = data.users;
                    this.lastUpdated = new Date().toLocaleTimeString('id-ID');
                } else {
                    this.fetchError = data.error ?? 'Gagal memuat data.';
                }
            } catch (e) {
                this.fetchError = e.message;
            } finally {
                this.loading = false;
            }
        },

        async disconnect(sessionId, username) {
            if (!confirm(`Putus koneksi user "${username}"?`)) return;
            this.disconnecting = sessionId;
            try {
                const res  = await fetch(this.disconnectUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ session_id: sessionId }),
                });
                const data = await res.json();
                if (data.success) {
                    this.users = this.users.filter(u => u['.id'] !== sessionId);
                } else {
                    alert('Gagal: ' + (data.error ?? 'Unknown error'));
                }
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.disconnecting = null;
            }
        },

        fmtBytes(n) {
            n = parseInt(n) || 0;
            if (n >= 1073741824) return (n / 1073741824).toFixed(1) + ' GB';
            if (n >= 1048576)    return (n / 1048576).toFixed(1) + ' MB';
            if (n >= 1024)       return (n / 1024).toFixed(1) + ' KB';
            return n + ' B';
        },
    };
}
</script>
@endpush
