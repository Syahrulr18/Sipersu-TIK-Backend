<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'penanda_tangan_id' => 'required|exists:users,id',
            'verifikator_id'    => 'required|exists:users,id',
            'tujuan_dosen_id'   => 'required',
            'kode_hal'          => 'required|exists:kode_hal,kode',
            'hal'               => 'required|string|min:3|max:500',
            'ringkasan'         => 'required|string|min:10',
            'lampiran.*'        => 'nullable|file|mimes:pdf,docx,jpg,jpeg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'penanda_tangan_id.required' => 'Penanda tangan wajib dipilih.',
            'verifikator_id.required'    => 'Verifikator wajib dipilih.',
            'tujuan_dosen_id.required'   => 'Tujuan/penerima wajib dipilih.',
            'kode_hal.required'          => 'Kode hal wajib dipilih.',
            'hal.required'               => 'Perihal wajib diisi.',
            'ringkasan.required'         => 'Ringkasan wajib diisi.',
            'lampiran.*.mimes'           => 'Lampiran hanya boleh berformat PDF, DOCX, JPG, JPEG.',
            'lampiran.*.max'             => 'Ukuran lampiran maksimal 5 MB.',
        ];
    }
}
