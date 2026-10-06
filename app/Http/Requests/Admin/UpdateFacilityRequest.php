<?php

namespace App\Http\Requests\Admin;

use App\Enums\FacilityStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role?->value === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:50'],
            'location' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'string', 'url', 'max:500'],
            'status' => ['required', Rule::enum(FacilityStatus::class)],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama fasilitas',
            'type' => 'tipe fasilitas',
            'location' => 'lokasi',
            'capacity' => 'kapasitas',
            'description' => 'deskripsi',
            'image_url' => 'tautan foto fasilitas',
            'status' => 'status',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama fasilitas wajib diisi.',
            'type.required' => 'Tipe fasilitas wajib diisi.',
            'location.required' => 'Lokasi fasilitas wajib diisi.',
            'capacity.required' => 'Kapasitas fasilitas wajib diisi.',
            'capacity.integer' => 'Kapasitas harus berupa bilangan bulat.',
            'capacity.min' => 'Kapasitas minimal bernilai 0.',
            'status.required' => 'Status fasilitas wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'image_url.url' => 'Format URL foto tidak valid.',
        ];
    }
}
