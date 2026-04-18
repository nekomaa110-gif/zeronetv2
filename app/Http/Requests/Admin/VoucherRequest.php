<?php

namespace App\Http\Requests\Admin;

use App\Services\VoucherService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'count'      => ['required', 'integer', 'min:1', 'max:100'],
            'type'       => ['required', Rule::in(array_keys(VoucherService::TYPES))],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'prefix'     => ['nullable', 'string', 'alpha_num', 'max:20'],
            'note'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'count.required'    => 'Jumlah voucher wajib diisi.',
            'count.max'         => 'Maksimal 100 voucher per generate.',
            'type.required'     => 'Tipe voucher wajib dipilih.',
            'type.in'           => 'Tipe voucher tidak valid.',
            'package_id.required' => 'Paket wajib dipilih.',
            'package_id.exists' => 'Paket tidak ditemukan.',
        ];
    }
}
