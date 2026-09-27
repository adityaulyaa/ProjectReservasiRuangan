<?php

namespace App\Http\Requests\User;

use App\Models\Facility;
use App\Services\ReservationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
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
        return [
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'max:255'],
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
            'reservation_date.required' => 'Tanggal reservasi wajib diisi.',
            'reservation_date.after_or_equal' => 'Tanggal reservasi tidak boleh tanggal yang sudah lewat.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:MM (contoh: 08:00).',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.date_format' => 'Format waktu selesai harus HH:MM (contoh: 09:30).',
            'end_time.after' => 'Waktu selesai harus lebih besar daripada waktu mulai.',
            'purpose.required' => 'Tujuan peminjaman wajib diisi.',
            'purpose.max' => 'Tujuan peminjaman maksimal 255 karakter.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $service = app(ReservationService::class);

            // 1. Validasi slot waktu (jam operasional & kelipatan 30 menit)
            $timeSlotErrors = $service->validateTimeSlot($this->start_time, $this->end_time);
            foreach ($timeSlotErrors as $error) {
                $validator->errors()->add('start_time', $error);
            }

            // 2. Validasi status fasilitas (harus active)
            $facility = Facility::find($this->facility_id);
            if ($facility) {
                $facilityError = $service->checkAvailableFacility($facility);
                if ($facilityError) {
                    $validator->errors()->add('facility_id', $facilityError);
                }
            }

            // 3. Validasi bentrok dengan reservasi yang sudah disetujui
            if ($facility && empty($timeSlotErrors)) {
                $hasConflict = $service->checkConflict(
                    (int) $this->facility_id,
                    $this->reservation_date,
                    $this->start_time,
                    $this->end_time
                );

                if ($hasConflict) {
                    $validator->errors()->add('start_time', 'Slot waktu yang dipilih bentrok dengan jadwal reservasi lain yang sudah disetujui.');
                }
            }
        });
    }
}
