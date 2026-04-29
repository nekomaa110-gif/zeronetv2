@extends('layouts.app')

@section('title', 'Recovery Codes 2FA')
@section('page-title', 'Recovery Codes 2FA')

@section('content')

    <x-admin.page-header
        title="Recovery Codes"
        description="Simpan kode-kode ini di tempat aman. Setiap kode hanya bisa digunakan satu kali."/>

    <div class="max-w-2xl">

        <div class="mb-4">
            <x-admin.alert type="warning" message="Kode ini hanya ditampilkan satu kali. Simpan baik-baik — gunakan untuk login jika HP/aplikasi authenticator hilang."/>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ count($codes) }} Recovery Codes</h3>
                <button type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('codes-block').innerText); this.innerText='Tersalin'; setTimeout(() => this.innerText='Salin Semua', 1500);"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Salin Semua
                </button>
            </div>

            <div id="codes-block" class="px-6 py-5 grid grid-cols-2 gap-2 font-mono text-sm text-gray-900 dark:text-white">
                @foreach ($codes as $code)
                    <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 text-center select-all">{{ $code }}</div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('profile.edit') }}"
                   class="inline-block px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Saya sudah simpan
                </a>
            </div>
        </div>
    </div>

@endsection
