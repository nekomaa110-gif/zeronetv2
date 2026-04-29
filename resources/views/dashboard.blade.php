@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    <x-admin.page-header title="Selamat Datang, {{ auth()->user()->name }}!" description="Ringkasan Status WiFi ZeroNet." />

    {{-- Stat Cards (live polling tiap 30 detik) --}}
    <div x-data="{
            stats: {
                total_users:        {{ $stats['total_users'] }},
                active_users:       {{ $stats['active_users'] }},
                online_sessions:    {{ $stats['online_sessions'] }},
                available_vouchers: {{ $stats['available_vouchers'] }}
            },
            init() { setInterval(() => this.poll(), 30000); },
            async poll() {
                try {
                    const r = await axios.get('{{ route('dashboard.stats') }}');
                    this.stats = r.data;
                } catch(e) {}
            }
         }"
         class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        {{-- Total User Hotspot --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total User Hotspot</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_users">{{ $stats['total_users'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- User Aktif --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.active_users">{{ $stats['active_users'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- User Online --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Online</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.online_sessions">{{ $stats['online_sessions'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Voucher Tersedia --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Voucher Tersedia</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.available_vouchers">{{ $stats['available_vouchers'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- Trafik Real-time per Router --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach ($routers as $router)
            <div
                x-data="trafficChart('{{ $router['name'] }}', '{{ route('routers.traffic', $router['id']) }}')"
                x-init="init()"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $router['name'] }}</p>
                            <p class="text-xs text-gray-400 font-mono">ether1 (WAN)</p>
                        </div>
                    </div>
                    <template x-if="online">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            Live
                        </span>
                    </template>
                    <template x-if="online === false">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            Offline
                        </span>
                    </template>
                    <template x-if="online === null">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Menghubungkan…
                        </span>
                    </template>
                </div>

                {{-- Current speed --}}
                <div class="grid grid-cols-2 gap-0 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700">
                    <div class="px-5 py-3">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Download</p>
                        <p class="text-xl font-bold text-red-500 dark:text-red-400 tabular-nums" x-text="currentDownload">—</p>
                    </div>
                    <div class="px-5 py-3">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Upload</p>
                        <p class="text-xl font-bold text-blue-500 dark:text-blue-400 tabular-nums" x-text="currentUpload">—</p>
                    </div>
                </div>

                {{-- Chart --}}
                <div class="px-3 pt-3 pb-2" style="height:170px;">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>
        @endforeach
    </div>

@endsection

@push('scripts')
<script src="{{ asset('assets/chart.umd.min.js') }}"></script>
<script>
function trafficChart(routerName, trafficUrl) {
    return {
        routerName,
        trafficUrl,
        online: null,          // null = belum tahu, true = live, false = offline
        currentDownload: '—',
        currentUpload: '—',
        _inFlight: false,      // guard anti-overlap
        _firstPoll: true,      // skip push 0 di poll pertama (belum ada delta)

        init() {
            this.$nextTick(() => {
                this.initChart();
                this.poll();
                this.$el._intervalId = setInterval(() => this.poll(), 3000);

                // Pause saat tab tidak aktif
                this._onVis = () => {
                    if (document.hidden) {
                        clearInterval(this.$el._intervalId);
                        this.$el._intervalId = null;
                    } else if (!this.$el._intervalId) {
                        this.poll();
                        this.$el._intervalId = setInterval(() => this.poll(), 3000);
                    }
                };
                document.addEventListener('visibilitychange', this._onVis);
            });
        },

        initChart() {
            const isDark   = document.documentElement.classList.contains('dark');
            const tickClr  = isDark ? '#9ca3af' : '#6b7280';
            const gridClr  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const fmtBps   = v => this.fmtBps(v);
            const POINTS   = 30;

            const ctx = this.$refs.canvas.getContext('2d');
            // Simpan di $el agar di luar reaktif Alpine
            this.$el._chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array(POINTS).fill(''),
                    datasets: [
                        {
                            label: 'Download',
                            data: Array(POINTS).fill(0),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.12)',
                            tension: 0.4,
                            pointRadius: 0,
                            borderWidth: 2,
                            fill: true,
                        },
                        {
                            label: 'Upload',
                            data: Array(POINTS).fill(0),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.12)',
                            tension: 0.4,
                            pointRadius: 0,
                            borderWidth: 2,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { intersect: false, mode: 'index' },
                    scales: {
                        x: { display: false },
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: gridClr },
                            ticks: {
                                color: tickClr,
                                font: { size: 10 },
                                maxTicksLimit: 4,
                                callback: fmtBps,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: tickClr,
                                boxWidth: 10,
                                boxHeight: 10,
                                padding: 12,
                                font: { size: 11 },
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: c => ' ' + c.dataset.label + ': ' + fmtBps(c.raw),
                                title: () => '',
                            },
                        },
                    },
                },
            });
        },

        async poll() {
            if (this._inFlight) return;           // poll sebelumnya masih jalan → skip
            this._inFlight = true;

            const ctl = new AbortController();
            const to  = setTimeout(() => ctl.abort(), 2500);

            try {
                const r = await fetch(this.trafficUrl, { signal: ctl.signal });
                const d = await r.json();
                if (!d.online) { this.online = false; return; }
                this.online = true;

                // Poll pertama: backend selalu return 0 (cache belum ada prev). Skip push.
                if (this._firstPoll) {
                    this._firstPoll = false;
                    return;
                }

                const chart = this.$el._chart;
                if (chart) {
                    chart.data.labels.push('');
                    chart.data.labels.shift();
                    chart.data.datasets[0].data.push(d.download);
                    chart.data.datasets[0].data.shift();
                    chart.data.datasets[1].data.push(d.upload);
                    chart.data.datasets[1].data.shift();
                    chart.update('none');
                }

                this.currentDownload = this.fmtBps(d.download);
                this.currentUpload   = this.fmtBps(d.upload);
            } catch (e) {
                this.online = false;
            } finally {
                clearTimeout(to);
                this._inFlight = false;
            }
        },

        fmtBps(bps) {
            if (bps >= 1_000_000) return (bps / 1_000_000).toFixed(2) + ' Mbps';
            if (bps >= 1_000)     return (bps / 1_000).toFixed(1)     + ' Kbps';
            return bps + ' bps';
        },
    };
}
</script>
@endpush
