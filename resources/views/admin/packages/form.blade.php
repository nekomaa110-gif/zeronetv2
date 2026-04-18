@extends('admin.layouts.app')

@section('title', $isUpdate ? 'Edit Paket: ' . $package->groupname : 'Tambah Paket')
@section('page-title', $isUpdate ? 'Edit Paket' : 'Tambah Paket')

@section('content')

@php
    if (old('attributes') !== null) {
        $initialAttrs = array_values(array_filter(old('attributes'), fn($a) => !empty($a['attribute'])));
    } elseif ($isUpdate) {
        $initialAttrs = $package->attributes->map(fn($a) => [
            'attribute'    => $a->attribute,
            'op'           => $a->op,
            'value'        => $a->value,
            'target_table' => $a->target_table,
        ])->values()->toArray();
    } else {
        $initialAttrs = [];
    }
    if (empty($initialAttrs)) {
        $initialAttrs = [['attribute' => '', 'op' => ':=', 'value' => '', 'target_table' => 'radgroupreply']];
    }
    $isActiveValue = old('is_active', $isUpdate ? ($package->is_active ? 1 : 0) : 1);
@endphp

    <x-admin.page-header :title="$isUpdate ? 'Edit Paket: ' . $package->groupname : 'Tambah Paket'">
        <x-slot:actions>
            <a href="{{ route('admin.packages.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                ← Kembali
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @php
        $action = $isUpdate
            ? route('admin.packages.update', $package->id)
            : route('admin.packages.store');
    @endphp

    <form method="POST" action="{{ $action }}" class="max-w-4xl space-y-5">
        @csrf
        @if($isUpdate) @method('PUT') @endif

        {{-- Informasi Paket --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">

            <div class="px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Paket</h3>
            </div>

            <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2">

                {{-- Nama Paket --}}
                <div>
                    <label for="groupname" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Paket <span class="text-red-500">*</span>
                    </label>
                    @if($isUpdate)
                        <input type="text" value="{{ $package->groupname }}" disabled
                               class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-400 cursor-not-allowed">
                        <input type="hidden" name="groupname" value="{{ $package->groupname }}">
                        <p class="mt-1 text-xs text-gray-400">Nama paket tidak dapat diubah.</p>
                    @else
                        <input type="text" id="groupname" name="groupname"
                               value="{{ old('groupname') }}"
                               placeholder="contoh: paket-10mbps"
                               class="w-full px-3 py-2.5 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                      {{ $errors->has('groupname') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                      focus:outline-none focus:ring-2 focus:border-transparent">
                        @error('groupname')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Huruf, angka, strip, titik, underscore. Dipakai sebagai groupname di RADIUS.</p>
                    @endif
                </div>

                {{-- Status toggle --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                    <label class="relative inline-flex items-center cursor-pointer gap-3 mt-1">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               class="sr-only peer" {{ $isActiveValue ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-700 rounded-full peer dark:bg-gray-700
                                    peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white
                                    after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                                    dark:border-gray-600 peer-checked:bg-brand-600"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                    </label>
                    <p class="mt-1.5 text-xs text-gray-400">Nonaktif = <code class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">Auth-Type := Reject</code></p>
                </div>

                {{-- Deskripsi --}}
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Deskripsi
                    </label>
                    <input type="text" id="description" name="description"
                           value="{{ old('description', $isUpdate ? $package->description : '') }}"
                           placeholder="contoh: Paket internet 10 Mbps"
                           class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                    @error('description')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Attribute Builder --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="packageBuilder({{ \Illuminate\Support\Js::from($initialAttrs) }})">

            <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Atribut RADIUS</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Atribut dikirim ke tabel RADIUS sesuai kolom Target.</p>
                </div>
                <button type="button" @click="addRow()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/40 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Baris
                </button>
            </div>

            {{-- Validation error banner for attributes --}}
            @php
                $attrErrors = collect($errors->toArray())
                    ->filter(fn($v, $k) => str_starts_with($k, 'attributes.'))
                    ->flatten();
            @endphp
            @if($attrErrors->isNotEmpty())
                <div class="mx-6 mt-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-xs text-red-600 dark:text-red-400">{{ $attrErrors->first() }}</p>
                </div>
            @endif

            <div class="overflow-x-auto p-4">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            <th class="px-2 pb-2 text-left font-medium">Atribut</th>
                            <th class="px-2 pb-2 text-left font-medium w-20">Op</th>
                            <th class="px-2 pb-2 text-left font-medium">Nilai</th>
                            <th class="px-2 pb-2 text-left font-medium w-36">Target</th>
                            <th class="px-2 pb-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="space-y-1">
                        <template x-for="(attr, i) in attrs" :key="i">
                            <tr class="group">
                                <td class="px-1 py-1">
                                    <input type="text"
                                           list="attr-presets"
                                           :name="`attributes[${i}][attribute]`"
                                           x-model="attr.attribute"
                                           placeholder="Mikrotik-Rate-Limit"
                                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                                </td>
                                <td class="px-1 py-1">
                                    <select :name="`attributes[${i}][op]`"
                                            x-model="attr.op"
                                            class="w-full px-2 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors font-mono">
                                        @foreach($operators as $op)
                                            <option value="{{ $op }}">{{ $op }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-1">
                                    <input type="text"
                                           :name="`attributes[${i}][value]`"
                                           x-model="attr.value"
                                           placeholder="nilai atribut"
                                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors font-mono">
                                </td>
                                <td class="px-1 py-1">
                                    <select :name="`attributes[${i}][target_table]`"
                                            x-model="attr.target_table"
                                            class="w-full px-2 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors font-mono">
                                        @foreach($targets as $tbl)
                                            <option value="{{ $tbl }}">{{ $tbl }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-1">
                                    <button type="button" @click="removeRow(i)"
                                            class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <datalist id="attr-presets">
                @foreach($presets as $preset)
                    <option value="{{ $preset }}">
                @endforeach
            </datalist>

            <div class="px-6 pb-5 pt-1">
                <p class="text-xs text-gray-400">
                    Ketik bebas atau pilih dari daftar preset. Target
                    <code class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">radgroupreply/check</code> = atribut grup,
                    <code class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">radreply/check</code> = atribut per-user (username = nama paket).
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pb-6">
            <button type="submit"
                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                {{ $isUpdate ? 'Simpan Perubahan' : 'Buat Paket' }}
            </button>
            <a href="{{ route('admin.packages.index') }}"
               class="px-5 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                Batal
            </a>
        </div>

    </form>

@endsection

@push('scripts')
<script>
function packageBuilder(initialAttrs) {
    return {
        attrs: initialAttrs,
        addRow() {
            this.attrs.push({ attribute: '', op: ':=', value: '', target_table: 'radgroupreply' });
        },
        removeRow(i) {
            this.attrs.splice(i, 1);
            if (this.attrs.length === 0) this.addRow();
        }
    };
}
</script>
@endpush
