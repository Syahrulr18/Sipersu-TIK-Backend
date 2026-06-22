<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKontenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'konten_html' => 'nullable|string',
            'konten_penerima' => 'nullable|array',
            'konten_penerima.*.penerima_id' => 'required_with:konten_penerima|integer',
            'konten_penerima.*.konten_html' => 'required_with:konten_penerima|string',
        ];
    }

    public function messages(): array
    {
        return [
            'konten_html.required' => 'Konten surat tidak boleh kosong.',
            'konten_penerima.array' => 'Format konten penerima tidak valid.',
        ];
    }
}
