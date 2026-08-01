<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'nik' => ['required', 'string', 'max:30', Rule::unique('employees', 'nik')->ignore($employeeId)],
            'name' => ['required', 'string', 'max:150'],
            'department_id' => ['required', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'join_date' => ['nullable', 'date'],
            'employment_status' => ['required', 'in:active,inactive,resigned'],
            'employee_type' => ['required', 'in:staff,dw,casual,trainee,outsourcing'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
