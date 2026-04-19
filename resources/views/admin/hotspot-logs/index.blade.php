@extends('admin.layouts.app')

@section('title', 'Log User Hotspot')
@section('page-title', 'Log User Hotspot')

@section('content')

    <x-admin.page-header
        title="Log User Hotspot"
        description="Riwayat autentikasi user hotspot ZeroNet."/>

    @php $hasFilter = (bool)($search || $status || $dateFrom || $dateTo); @endphp
    @php $liveMode  = !$hasFilter && $logs->currentPage() === 1; @endphp

    <x-admin.table>

        {{-- Filter bar --}}
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.hotspot-logs.index') }}">
                <div class="flex flex-wrap items-center gap-3">

                    {{-- Search username --}}
                    <div class="relative w-52">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari username..."
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                    </div>

                    {{-- Tanggal dari --}}
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           title="Dari tanggal"
                           class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">

                    <span class="text-gray-400 text-xs">—</span>

                    {{-- Tanggal sampai --}}
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           title="Sampai tanggal"
                           class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">

                    {{-- Filter status --}}
                    <select name="status"
                            class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        <option value="">Semua Status</option>
                        <option value="success" @selected($status === 'success')>Berhasil</option>
                        <option value="failed"  @selected($status === 'failed')>Gagal</option>
                    </select>

                    <button type="submit"
                            class="px-4 py-2 text-sm bg-brand-600 hover:bg-brand-700 text-white rounded-lg transition-colors">
                        Filter
                    </button>

                    @if($hasFilter)
                        <a href="{{ route('admin.hotspot-logs.index') }}"
                           class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 underline underline-offset-2">
                            Reset
                        </a>
                    @endif

                    {{-- Live indicator (hanya muncul di mode live: halaman 1, tanpa filter) --}}
                    @if($liveMode)
                        <div class="ml-auto flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            Live
                        </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel dengan AJAX live polling --}}
        <div class="overflow-x-auto"
             @if($liveMode)
             x-data="{
                 lastId: {{ $logs->first()?->id ?? 0 }},
                 async poll() {
                     if (!this.lastId) return;
                     try {
                         const { data } = await axios.get('{{ route('admin.hotspot-logs.poll') }}', { params: { after: this.lastId } });
                         if (data.count > 0) {
                             this.$refs.tbody.insertAdjacentHTML('afterbegin', data.html);
                             this.lastId = data.max_id;
                         }
                     } catch(e) {}
                 },
                 init() { setInterval(() => this.poll(), 15000); }
             }"
             @endif>
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/60 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium w-10">#</th>
                        <th class="px-5 py-3 font-medium">Username</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Jam</th>
                        <th class="px-5 py-3 font-medium text-center">Status</th>
                        <th class="px-5 py-3 font-medium">Keterangan</th>
                        <th class="px-5 py-3 font-medium">NAS / IP Klien</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700"
                       @if($liveMode) x-ref="tbody" @endif>
                    @forelse($logs as $log)
                        @include('admin.hotspot-logs._row', [
                            'log'           => $log,
                            'rejectReasons' => $rejectReasons,
                            'userIps'       => $userIps,
                            'rowNumber'     => $logs->firstItem() + $loop->index,
                            'isNew'         => false,
                        ])
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">
                                        @if($hasFilter)
                                            Tidak ada log yang cocok dengan filter.
                                        @else
                                            Belum ada log autentikasi.
                                        @endif
                                    </p>
                                    @if($hasFilter)
                                        <a href="{{ route('admin.hotspot-logs.index') }}"
                                           class="text-sm text-brand-600 hover:underline">Reset filter →</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif

    </x-admin.table>

@endsection

@push('scripts')
<style>
    .log-row-new { animation: logRowFade 5s ease-in-out forwards; }
    @keyframes logRowFade {
        0%, 60% { background-color: rgb(240 253 244); }
        100%     { background-color: transparent; }
    }
    .dark .log-row-new { animation: logRowFadeDark 5s ease-in-out forwards; }
    @keyframes logRowFadeDark {
        0%, 60% { background-color: rgba(20, 83, 45, 0.15); }
        100%     { background-color: transparent; }
    }
</style>
@endpush
