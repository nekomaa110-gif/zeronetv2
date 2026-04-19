@extends('admin.layouts.app')

@section('title', 'Generate Voucher')
@section('page-title', 'Generate Voucher')

@section('content')

    <x-admin.page-header
        title="Generate Voucher"
        description="Buat voucher baru dan print dengan otomatis.">
        <x-slot:actions>
            <a href="{{ route('admin.vouchers.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="max-w-xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Form Generate Voucher</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kode 8-karakter dibuat otomatis,dan langsung disinkronkan ke Database ZeroNet.</p>
            </div>

            <form method="POST" action="{{ route('admin.vouchers.store') }}" class="px-6 py-5 space-y-6">
                @csrf

                {{-- Tipe Voucher --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Tipe Voucher <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach($types as $key => $cfg)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="type" value="{{ $key }}"
                                       {{ old('type', '4h') === $key ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="flex flex-col gap-1.5 px-4 py-3.5 rounded-xl border-2 transition-all cursor-pointer
                                            border-gray-200 dark:border-gray-700
                                            peer-checked:border-brand-500 peer-checked:bg-brand-50 dark:peer-checked:bg-brand-900/20
                                            hover:border-gray-300 dark:hover:border-gray-600">
                                    <span class="font-bold text-base text-gray-900 dark:text-white">{{ $cfg['label'] }}</span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 leading-snug">{{ $cfg['description'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('type')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Paket / Profile --}}
                <div>
                    <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Paket / Profile <span class="text-red-500">*</span>
                    </label>
                    <select id="package_id" name="package_id"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   {{ $errors->has('package_id') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                   focus:outline-none focus:ring-2 focus:border-transparent">
                        <option value="">-- Pilih Paket --</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                                {{ $pkg->groupname }}{{ $pkg->description ? ' — ' . $pkg->description : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @if($packages->isEmpty())
                        <p class="mt-1.5 text-xs text-yellow-600 dark:text-yellow-400">
                            Belum ada paket aktif.
                            <a href="{{ route('admin.packages.create') }}" class="underline">Buat paket dahulu →</a>
                        </p>
                    @endif
                </div>

                {{-- Prefix Username --}}
                <div>
                    <label for="prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Prefix Username <span class="text-xs font-normal text-gray-400">(opsional)</span>
                    </label>
                    <div class="flex items-center gap-0">
                        <input type="text" id="prefix" name="prefix"
                               value="{{ old('prefix') }}"
                               placeholder="Contoh: ZERO"
                               maxlength="20"
                               class="w-36 px-3 py-2.5 text-sm rounded-l-lg border {{ $errors->has('prefix') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                      border-r-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 uppercase
                                      focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                               style="text-transform:uppercase"
                               oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'')">
                        <span class="px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-r-lg">
                            XXXX
                        </span>
                    </div>
                    @error('prefix')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Jika diisi, username menjadi <strong>PREFIXKODE</strong>. Kosongkan untuk format <strong>XXXXXX</strong> (6 karakter).</p>
                </div>

                {{-- Jumlah --}}
                <div>
                    <label for="count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Jumlah Voucher <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="count" name="count"
                           value="{{ old('count', 1) }}" min="1" max="100"
                           class="w-full px-3 py-2.5 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  {{ $errors->has('count') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                  focus:outline-none focus:ring-2 focus:border-transparent">
                    @error('count')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Maksimal 100 voucher per generate.</p>
                </div>

                {{-- Catatan --}}
                <div>
                    <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Catatan <span class="text-xs font-normal text-gray-400">(opsional)</span>
                    </label>
                    <input type="text" id="note" name="note"
                           value="{{ old('note') }}"
                           placeholder="Contoh: Batch acara 18 April"
                           class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                            class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Generate Voucher
                    </button>
                    <a href="{{ route('admin.vouchers.index') }}"
                       class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
