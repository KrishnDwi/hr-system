<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Imports\EmployeesImport;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index()
    {
        $departments = Department::active()->orderBy('name')->get();

        return view('employees.index', compact('departments'));
    }

    /**
     * Endpoint JSON untuk DataTables server-side, dengan filter departemen & status.
     */
    public function data(Request $request)
    {
        $query = Employee::with('department')->select('employees.*');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        $employees = $query->orderBy('name')->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'nik' => $employee->nik,
                'name' => $employee->name,
                'department' => $employee->department->name,
                'position' => $employee->position,
                'employee_type' => $employee->employee_type,
                'employment_status' => $employee->employment_status,
                'email' => $employee->email,
                'phone' => $employee->phone,
            ];
        });

        return response()->json(['data' => $employees]);
    }

    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();

        return view('employees.create', compact('departments'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        Employee::create($request->validated());

        return redirect()
            ->route('employees.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::active()->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()
            ->route('employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * "Nonaktifkan" sesuai requirement — bukan hard delete, supaya riwayat
     * training karyawan tetap utuh untuk pelaporan historis.
     */
    public function destroy(Employee $employee)
    {
        $employee->update(['employment_status' => 'inactive']);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Karyawan berhasil dinonaktifkan. Riwayat training tetap tersimpan.');
    }

    public function showImportForm()
    {
        return view('employees.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new EmployeesImport();
        Excel::import($import, $request->file('file'));

        $errors = $import->allErrors();
        $totalProcessed = $import->totalProcessed();

        if (!empty($errors)) {
            return redirect()
                ->route('employees.index')
                ->with('warning', "Import selesai — {$totalProcessed} baris berhasil diproses, " . count($errors) . ' baris/catatan bermasalah.')
                ->with('import_failures', $errors);
        }

        return redirect()
            ->route('employees.index')
            ->with('success', "Import berhasil — {$totalProcessed} baris data karyawan diproses, termasuk riwayat sertifikasi yang terdeteksi.");
    }
}
