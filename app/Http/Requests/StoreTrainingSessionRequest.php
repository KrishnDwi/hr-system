<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // tidak ada role/permission di v1
    }

    public function rules(): array
    {
        return [
            'training_module_id' => ['required', 'exists:training_modules,id'],
            'trainer_name' => ['required', 'string', 'max:150'],
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'actual_duration_hours' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'location' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],

            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['exists:employees,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Pilih minimal satu peserta training.',
            'end_time.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ];
    }
}
