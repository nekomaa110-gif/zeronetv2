@extends('admin.layouts.app')

@section('title', 'Voucher')
@section('page-title', 'Voucher')

@section('content')

    <x-admin.page-header title="Voucher" description="Kelola voucher hotspot ZeroNet.">
        <x-slot:actions>
            <button id="btn-print-selected" type="button" onclick="printSelected()"
                    class="hidden inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span id="btn-print-label">Print Terpilih</span>
            </button>
            <a href="{{ route('admin.vouchers.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Generate Voucher
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table>

        {{-- Filter bar --}}
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.vouchers.index') }}">
                <div class="flex flex-wrap items-center gap-3">

                    <div class="relative w-48">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari kode / catatan..."
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                    </div>

                    <select name="status"
                            class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        <option value="">Semua Status</option>
                        <option value="ready"    @selected($status === 'ready')>Ready</option>
                        <option value="active"   @selected($status === 'active')>Aktif</option>
                        <option value="expired"  @selected($status === 'expired')>Expired</option>
                        <option value="disabled" @selected($status === 'disabled')>Nonaktif</option>
                    </select>

                    <select name="type"
                            class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        <option value="">Semua Tipe</option>
                        @foreach($types as $key => $cfg)
                            <option value="{{ $key }}" @selected($type === $key)>{{ $cfg['label'] }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="px-4 py-2 text-sm bg-brand-600 hover:bg-brand-700 text-white rounded-lg transition-colors">
                        Filter
                    </button>

                    @if($search || $status || $type)
                        <a href="{{ route('admin.vouchers.index') }}"
                           class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 underline underline-offset-2">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/60 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium w-10">
                            <input type="checkbox" id="check-all"
                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-brand-600 focus:ring-brand-500 bg-white dark:bg-gray-800">
                        </th>
                        <th class="px-5 py-3 font-medium">Username</th>
                        <th class="px-5 py-3 font-medium">Password</th>
                        <th class="px-5 py-3 font-medium">Tipe</th>
                        <th class="px-5 py-3 font-medium">Paket</th>
                        <th class="px-5 py-3 font-medium text-center">Status</th>
                        <th class="px-5 py-3 font-medium">Login Pertama</th>
                        <th class="px-5 py-3 font-medium">Expired</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">

                            <td class="px-5 py-3.5">
                                <input type="checkbox" value="{{ $v->id }}"
                                       class="voucher-checkbox w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-brand-600 focus:ring-brand-500 bg-white dark:bg-gray-800">
                            </td>

                            <td class="px-5 py-3.5">
                                <span class="font-mono font-bold text-gray-900 dark:text-white tracking-widest">{{ $v->code }}</span>
                                @if($v->note)
                                    <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[160px]">{{ $v->note }}</p>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                <span class="font-mono font-bold text-lg tracking-widest text-gray-800 dark:text-gray-200">{{ $v->password ?? '—' }}</span>
                            </td>

                            <td class="px-5 py-3.5 text-xs whitespace-nowrap">
                                @php $typeInfo = $types[$v->type] ?? null; @endphp
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $typeInfo['label'] ?? $v->type }}</span>
                            </td>

                            <td class="px-5 py-3.5">
                                @if($v->package)
                                    <x-admin.badge color="purple">{{ $v->package->groupname }}</x-admin.badge>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                @switch($v->status)
                                    @case('ready')
                                        <x-admin.badge color="gray">Ready</x-admin.badge>
                                        @break
                                    @case('active')
                                        <x-admin.badge color="green" :dot="true">Aktif</x-admin.badge>
                                        @break
                                    @case('expired')
                                        <x-admin.badge color="red">Expired</x-admin.badge>
                                        @break
                                    @case('disabled')
                                        <x-admin.badge color="yellow">Nonaktif</x-admin.badge>
                                        @break
                                @endswitch
                            </td>

                            <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                @if($v->first_login_at)
                                    <div>{{ $v->first_login_at->format('d M Y') }}</div>
                                    <div class="font-mono text-gray-400">{{ $v->first_login_at->format('H:i') }}</div>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-xs">
                                @if($v->expired_at)
                                    <div class="{{ $v->status === 'expired' ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $v->expired_at->format('d M Y') }}
                                    </div>
                                    <div class="font-mono text-gray-400">{{ $v->expired_at->format('H:i') }}</div>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- Print single --}}
                                    <a href="{{ route('admin.vouchers.print', ['ids' => $v->id]) }}"
                                       target="_blank" title="Print"
                                       class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>

                                    {{-- Enable (admin only, hanya jika disabled) --}}
                                    @if($v->status === 'disabled' && auth()->user()->role === 'admin')
                                        <form method="POST" action="{{ route('admin.vouchers.enable', $v) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="Aktifkan kembali"
                                                    class="p-1.5 rounded-lg text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Disable (admin + operator, hanya jika ready/active) --}}
                                    @if(in_array($v->status, ['ready', 'active']))
                                        <form method="POST" action="{{ route('admin.vouchers.disable', $v) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="Nonaktifkan"
                                                    class="p-1.5 rounded-lg text-yellow-500 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Delete (admin only) --}}
                                    @if(auth()->user()->role === 'admin')
                                        <form method="POST" action="{{ route('admin.vouchers.destroy', $v) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                    class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">
                                        @if($search || $status || $type)
                                            Tidak ada voucher yang cocok dengan filter.
                                        @else
                                            Belum ada voucher.
                                        @endif
                                    </p>
                                    @if($search || $status || $type)
                                        <a href="{{ route('admin.vouchers.index') }}" class="text-sm text-brand-600 hover:underline">Reset filter →</a>
                                    @else
                                        <a href="{{ route('admin.vouchers.create') }}" class="text-sm text-brand-600 hover:underline">Generate voucher pertama →</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $vouchers->withQueryString()->links() }}
            </div>
        @endif

    </x-admin.table>

@endsection

@push('scripts')
<script>
    (function () {
        var checkAll   = document.getElementById('check-all');
        var checkboxes = document.querySelectorAll('.voucher-checkbox');
        var btnPrint   = document.getElementById('btn-print-selected');
        var btnLabel   = document.getElementById('btn-print-label');

        function updatePrintBtn() {
            var selected = document.querySelectorAll('.voucher-checkbox:checked');
            if (selected.length > 0) {
                btnPrint.classList.remove('hidden');
                btnLabel.textContent = 'Print Terpilih (' + selected.length + ')';
            } else {
                btnPrint.classList.add('hidden');
            }
            checkAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
            checkAll.checked = selected.length === checkboxes.length && checkboxes.length > 0;
        }

        checkAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) { cb.checked = checkAll.checked; });
            updatePrintBtn();
        });

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', updatePrintBtn);
        });

        window.printSelected = function () {
            var ids = Array.from(document.querySelectorAll('.voucher-checkbox:checked'))
                           .map(function (cb) { return cb.value; })
                           .join(',');
            if (!ids) return;
            window.open('{{ route('admin.vouchers.print') }}?ids=' + ids, '_blank');
        };
    })();
</script>
@endpush
