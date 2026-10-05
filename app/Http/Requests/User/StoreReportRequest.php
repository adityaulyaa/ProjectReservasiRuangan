<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $role = is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role;

        return $role === 'user';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categories = config('reservation.report_categories', ['listrik', 'ac', 'furniture', 'plumbing', 'it', 'lainnya']);
        $maxKb = config('reservation.photo_max_kb', 2048);
        $mimes = implode(',', config('reservation.photo_mimes', ['jpg', 'jpeg', 'png']));

        return [
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'category' => ['required', 'string', Rule::in($categories)],
            'description' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:'.$mimes, 'max:'.$maxKb],
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'facility_id.required' => 'Fasilitas wajib dipilih.',
            'facility_id.exists' => 'Fasilitas yang dipilih tidak valid.',
            'category.required' => 'Kategori kerusakan wajib dipilih.',
            'category.in' => 'Kategori kerusakan tidak valid.',
            'description.required' => 'Deskripsi kerusakan wajib diisi.',
            'description.max' => 'Deskripsi kerusakan maksimal 2000 karakter.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.mimes' => 'Format foto harus berupa jpg, jpeg, atau png.',
            'photo.max' => 'Ukuran foto maksimal 2MB (2048 KB).',
        ];
    }
}
