<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Identitas dasar
            'nik' => ['required', 'string', 'max:30', 'unique:employees,nik'],
            'name' => ['required', 'string', 'max:150'],
            'department_id' => ['required', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'join_date' => ['nullable', 'date'],
            'employment_status' => ['required', 'in:active,inactive,resigned'],
            'employee_type' => ['required', 'in:staff,dw,casual,trainee,outsourcing'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],

            // Demografi personal
            'place_of_birth' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'marital_status_tax' => ['nullable', 'string', 'max:30'],
            'job_level' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'region' => ['nullable', 'string', 'max:100'],
            'annual_leave_entitlement' => ['nullable', 'integer', 'min:0', 'max:60'],

            // Keluarga
            'spouse_name' => ['nullable', 'string', 'max:150'],
            'spouse_date_of_birth' => ['nullable', 'date'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],

            // Finansial / legal
            'npwp_no' => ['nullable', 'string', 'max:30'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'nik_ktp' => ['nullable', 'string', 'max:20'],
            'jamsostek_no' => ['nullable', 'string', 'max:30'],
            'bpjs_no' => ['nullable', 'string', 'max:30'],

            // Pendidikan
            'education_background' => ['nullable', 'string', 'max:150'],
            'education_level' => ['nullable', 'string', 'max:30'],

            // Akses Portal Karyawan (opsional — kalau diisi, karyawan bisa login)
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }
}
