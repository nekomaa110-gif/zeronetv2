<?php

namespace App\Http\Requests\Admin;

use App\Models\RadCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RadiusUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate  = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $username  = $this->route('radius_user'); // nama param route

        return [
            'username' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-zA-Z0-9._\-@]+$/',
                // Saat create: pastikan username belum ada di radcheck (cek semua atribut)
                $isUpdate ? Rule::exists('radcheck', 'username') : function ($attr, $val, $fail) {
                    if (RadCheck::where('username', $val)->exists()) {
                        $fail('Username sudah digunakan.');
                    }
                },
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:1',
                'max:128',
            ],
            'group'  => ['nullable', 'string', 'max:64'],
            'expiry' => ['nullable', 'date_format:d M Y'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required'     => 'Username wajib diisi.',
            'username.regex'        => 'Username hanya boleh huruf, angka, titik, strip, dan @.',
            'username.max'          => 'Username maksimal 64 karakter.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password tidak boleh kosong.',
            'expiry.date_format'    => 'Format tanggal expire: DD Mon YYYY (contoh: 31 Dec 2025).',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'username',
            'password' => 'password',
            'group'    => 'profil/paket',
            'expiry'   => 'tanggal expire',
        ];
    }
}
