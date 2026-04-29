@extends('layouts.app')

@section('title', 'Aktifkan 2FA')
@section('page-title', 'Aktifkan 2FA')

@section('content')

    <x-admin.page-header
        title="Aktifkan Two-Factor Authentication"
        description="Tambahkan lapisan keamanan ekstra dengan kode dari aplikasi authenticator."/>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Langkah-langkah</h3>
                <ol class="mt-3 space-y-1.5 text-xs text-gray-500 dark:text-gray-400 list-decimal list-inside">
                    <li>Install aplikasi authenticator (Google Authenticator, Authy, Microsoft Authenticator).</li>
                    <li>Scan QR code di bawah, atau masukkan secret key secara manual.</li>
                    <li>Masukkan kode 6 digit yang muncul di aplikasi untuk konfirmasi.</li>
                </ol>
            </div>

            <div class="px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">

                <div class="flex flex-col items-center gap-3">
                    <div class="p-3 bg-white border border-gray-200 dark:border-gray-700 rounded-xl">
                        {!! $qrSvg !!}
                    </div>
                    <p class="text-xs text-gray-400 text-center">Scan dengan aplikasi authenticator</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Secret Key (input manual)
                        </label>
                        <div class="flex items-stretch gap-2">
                            <input type="text" readonly value="{{ $secret }}"
                                   class="flex-1 px-3 py-2.5 text-sm font-mono rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none">
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $secret }}'); this.innerText='Tersalin'; setTimeout(() => this.innerText='Salin', 1500);"
                                    class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                Salin
                            </button>
                        </div>
                    </div>

                    @if ($errors->any())
                        <x-admin.alert type="error" :message="$errors->first()"/>
                    @endif

                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Kode dari Authenticator <span class="text-red-500">*</span>
                            </label>
                            <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*"
                                   maxlength="6" autocomplete="one-time-code" required autofocus
                                   placeholder="123456"
                                   class="w-full px-3 py-2.5 text-center text-base tracking-[0.3em] font-mono rounded-lg border bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                          {{ $errors->has('code') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                          focus:outline-none focus:ring-2 focus:border-transparent">
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Konfirmasi & Aktifkan
                            </button>
                            <a href="{{ route('profile.edit') }}"
                               class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

@endsection
