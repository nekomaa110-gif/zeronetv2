@php
    $success  = $log->isSuccess();
    $nasIp    = $log->nasipaddress ?? '';
    if ($nasIp === 'localhost') $nasIp = '127.0.0.1';
    $clientIp = $userIps[$log->username] ?? '';
    $isNew    = $isNew ?? false;
@endphp
<tr class="{{ $isNew ? 'log-row-new' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">

    <td class="px-5 py-3.5 text-gray-400 dark:text-gray-500 text-xs">
        @if($isNew)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold
                         bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 leading-none">baru</span>
        @else
            {{ $rowNumber ?? '—' }}
        @endif
    </td>

    <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
        {{ $log->username }}
    </td>

    <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
        {{ $log->authdate?->format('d M Y') }}
    </td>

    <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 text-xs font-mono">
        {{ $log->authdate?->format('H:i:s') }}
    </td>

    <td class="px-5 py-3.5 text-center">
        @if($success)
            <x-admin.badge color="green" :dot="true">Berhasil</x-admin.badge>
        @else
            <x-admin.badge color="red" :dot="true">Gagal</x-admin.badge>
        @endif
    </td>

    <td class="px-5 py-3.5 text-xs">
        @if($success)
            <span class="text-green-600 dark:text-green-400">Login berhasil</span>
        @else
            @php $reason = $rejectReasons[$log->username] ?? 'Access-Reject'; @endphp
            @if($reason === 'Voucher sudah expired')
                <span class="text-orange-500 dark:text-orange-400">{{ $reason }}</span>
            @elseif($reason === 'Akun dinonaktifkan')
                <span class="text-yellow-600 dark:text-yellow-400">{{ $reason }}</span>
            @elseif($reason === 'Akun sedang digunakan')
                <span class="text-blue-500 dark:text-blue-400">{{ $reason }}</span>
            @else
                <span class="text-red-500 dark:text-red-400">{{ $reason }}</span>
            @endif
        @endif
    </td>

    <td class="px-5 py-3.5 text-xs font-mono">
        @if($nasIp !== '' || $clientIp !== '')
            <div class="space-y-1">
                @if($nasIp !== '')
                    <div class="flex items-center gap-1.5">
                        <span class="font-sans text-[10px] font-semibold w-7 text-center py-0.5 rounded
                                     bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400
                                     leading-none tracking-wide">NAS</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $nasIp }}</span>
                    </div>
                @endif
                @if($clientIp !== '')
                    <div class="flex items-center gap-1.5">
                        <span class="font-sans text-[10px] font-semibold w-7 text-center py-0.5 rounded
                                     bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400
                                     leading-none tracking-wide">IP</span>
                        <span class="text-green-600 dark:text-green-400">{{ $clientIp }}</span>
                    </div>
                @endif
            </div>
        @else
            <span class="text-gray-300 dark:text-gray-600">—</span>
        @endif
    </td>

</tr>
