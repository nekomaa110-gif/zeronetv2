@extends('layouts.app')

@section('title', 'Manajemen Router')
@section('page-title', 'Manajemen Router')

@section('content')

    <x-admin.page-header title="Manajemen Router" description="Monitor dan kelola router MikroTik via tunnel VPN." />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach ($routers as $router)
            <div
                x-data="routerCard('{{ route('routers.stats', $router['id']) }}', '{{ route('routers.show', $router['id']) }}')"
                x-init="load()"
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $router['name'] }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $router['host'] }}:{{ $router['port'] }}</p>
                        </div>
                    </div>

                    {{-- Status badge --}}
                    <div>
                        <template x-if="loading">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-400 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                Mengecek...
                            </span>
                        </template>
                        <template x-if="!loading && online">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                Online
                            </span>
                        </template>
                        <template x-if="!loading && !online">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Offline
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Stats (shown when online) --}}
                <div class="px-5 py-4">
                    <template x-if="loading">
                        <div class="grid grid-cols-2 gap-3">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="h-12 rounded-lg bg-gray-100 dark:bg-gray-700 animate-pulse"></div>
                            @endfor
                        </div>
                    </template>

                    <template x-if="!loading && online">
                        <div class="space-y-3">
                            {{-- Identity & Uptime --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-400 mb-0.5">Identitas</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white truncate" x-text="stats.identity"></p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-400 mb-0.5">Waktu Aktif</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white" x-text="fmtUptime(stats.uptime)"></p>
                                </div>
                            </div>

                            {{-- CPU --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">CPU</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300" x-text="stats.cpu_load + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all"
                                        :class="stats.cpu_load > 80 ? 'bg-red-500' : stats.cpu_load > 50 ? 'bg-yellow-500' : 'bg-green-500'"
                                        :style="'width:' + stats.cpu_load + '%'"></div>
                                </div>
                            </div>

                            {{-- RAM --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">RAM</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300" x-text="stats.mem_pct + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all"
                                        :class="stats.mem_pct > 80 ? 'bg-red-500' : stats.mem_pct > 60 ? 'bg-yellow-500' : 'bg-blue-500'"
                                        :style="'width:' + stats.mem_pct + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="!loading && !online">
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-400 dark:text-gray-500">Router tidak dapat dijangkau</p>
                            <p class="text-xs text-gray-300 dark:text-gray-600 mt-1" x-text="error"></p>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    <a :href="detailUrl"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 transition-colors">
                        Lihat Detail
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

@endsection

@push('scripts')
<script>
function routerCard(statsUrl, detailUrl) {
    return {
        statsUrl,
        detailUrl,
        loading: true,
        online: false,
        stats: {},
        error: '',
        async load() {
            try {
                const res = await fetch(statsUrl);
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
        fmtUptime(str) {
            if (!str) return '-';
            const label = { w: 'mg', d: 'hr', h: 'j', m: 'm' };
            const parts = [];
            for (const [, num, unit] of str.matchAll(/(\d+)([wdhms])/g)) {
                if (label[unit]) parts.push(num + label[unit]);
            }
            return parts.join(' ') || '-';
        },
    };
}
</script>
@endpush
