<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:training_modules,code'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_mandatory' => ['required', 'boolean'],
            'standard_duration_hours' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'validity_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
