@extends('admin.layouts.app')

@section('title', 'WhatsApp Gateway')
@section('page-title', 'WhatsApp Gateway')

@php
    $st = $status['status'] ?? 'unknown';
    $statusBadge = match($st) {
        'open'             => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
        'qr', 'connecting' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
        default            => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
    };
    $inputCls = 'w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors';
    $btnPrimary = 'inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors';
    $btnDanger  = 'inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors';
    $btnNeutral = 'inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors';
@endphp

@section('content')
<div class="p-4 md:p-6 space-y-6">

    <x-admin.page-header title="WhatsApp Gateway"
                         description="Kirim pesan WhatsApp dan kelola kontak pelanggan untuk reminder otomatis.">
        <x-slot:actions>
            <a href="/wa-admin" target="_blank"
               class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/40 rounded-lg transition-colors">
                Buka panel QR
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Status badge --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                Status: {{ strtoupper($st) }}
            </span>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('ok'))
        <div class="rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 px-4 py-3 text-sm">
            {{ session('ok') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Manual send --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Kirim Manual</h3>
            <form method="post" action="{{ route('admin.whatsapp.send') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nomor tujuan</label>
                    <input name="number" required placeholder="08xxxxxxxxxx" class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pesan</label>
                    <textarea name="message" rows="5" required placeholder="Isi pesan..." class="{{ $inputCls }}"></textarea>
                </div>
                <button type="submit" class="{{ $btnPrimary }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim via Queue
                </button>
            </form>
        </div>

        {{-- Tambah kontak --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Tambah Kontak Pelanggan</h3>
            <form method="post" action="{{ route('admin.whatsapp.contacts.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username <span class="text-gray-400">(sama dengan radcheck.username)</span></label>
                    <input name="username" required placeholder="contoh: budi" class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nomor WhatsApp</label>
                    <input name="phone" required placeholder="08xxxxxxxxxx" class="{{ $inputCls }}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama <span class="text-gray-400">(opsional)</span></label>
                        <input name="name" placeholder="Nama pelanggan" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan <span class="text-gray-400">(opsional)</span></label>
                        <input name="notes" placeholder="catatan internal" class="{{ $inputCls }}">
                    </div>
                </div>
                <button type="submit" class="{{ $btnPrimary }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Kontak
                </button>
            </form>
        </div>
    </div>

    {{-- List kontak --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Kontak</h3>
            <form method="get" class="flex gap-2">
                <input name="q" value="{{ $q }}" placeholder="cari username/phone/nama"
                       class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                <button class="{{ $btnNeutral }} text-xs px-3 py-1.5">Cari</button>
            </form>
        </div>

        {{-- Forms (HTML5 form= attribute trick) --}}
        @foreach($contacts as $c)
            <form id="upd-{{ $c->id }}" method="post" action="{{ route('admin.whatsapp.contacts.update', $c) }}">@csrf @method('PATCH')</form>
            <form id="del-{{ $c->id }}" method="post" action="{{ route('admin.whatsapp.contacts.destroy', $c) }}"
                  onsubmit="return confirm('Hapus kontak {{ $c->username }}?');">@csrf @method('DELETE')</form>
        @endforeach

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium">Username</th>
                        <th class="text-left px-4 py-3 font-medium">Nama</th>
                        <th class="text-left px-4 py-3 font-medium">Phone</th>
                        <th class="text-left px-4 py-3 font-medium">Notes</th>
                        <th class="text-left px-4 py-3 font-medium">Reminder Terakhir</th>
                        <th class="text-left px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                    @forelse($contacts as $c)
                    @php
                        $cellInput = 'w-full px-2 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-brand-500';
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">{{ $c->username }}</td>
                        <td class="px-4 py-3"><input form="upd-{{ $c->id }}" name="name"  value="{{ $c->name }}"  class="{{ $cellInput }} min-w-[140px]"></td>
                        <td class="px-4 py-3"><input form="upd-{{ $c->id }}" name="phone" value="{{ $c->phone }}" required class="{{ $cellInput }} min-w-[140px]"></td>
                        <td class="px-4 py-3"><input form="upd-{{ $c->id }}" name="notes" value="{{ $c->notes }}" class="{{ $cellInput }} min-w-[160px]"></td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $c->reminder_sent_at?->format('d M Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex gap-1.5">
                                <button form="upd-{{ $c->id }}" class="inline-flex items-center px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium rounded-lg transition-colors">Update</button>
                                <button form="del-{{ $c->id }}" class="{{ $btnDanger }}">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada kontak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700">{{ $contacts->links() }}</div>
    </div>
</div>
@endsection
