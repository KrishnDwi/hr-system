<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Exports\EmployeeMasterExport;
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
        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        Employee::create($validated);

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
        $validated = $request->validated();

        // Password dikosongkan di form = TIDAK diubah, bukan dihapus.
        // Kalau HRD memang ingin karyawan tidak bisa login, gunakan
        // employment_status (inactive/resigned) — itu juga otomatis
        // memblokir login (lihat EmployeeAuthController).
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $employee->update($validated);

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

    /**
     * Detail karyawan + riwayat training (sudah dilakukan) + mandatory
     * training yang belum/perlu dilakukan (belum pernah ATAU sudah expired).
     */
    public function show(Employee $employee)
    {
        $employee->load('department', 'contracts');

        $trainingHistories = $employee->trainingHistories()
            ->orderByDesc('training_date')
            ->get();

        $missingMandatoryModules = $employee->missingMandatoryModules();

        return view('employees.show', compact('employee', 'trainingHistories', 'missingMandatoryModules'));
    }

    public function showImportForm()
    {
        return view('employees.import');
    }

    /**
     * Export SATU baris per karyawan, semua field profil HR (bukan riwayat
     * training seperti Report). Filter mengikuti filter yang sedang aktif
     * di halaman index (departemen/status/kategori), dikirim lewat query string.
     */
    public function exportMaster(Request $request)
    {
        $query = Employee::with('department', 'contracts')->orderBy('name');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        return Excel::download(
            new EmployeeMasterExport($query),
            'data-karyawan-lengkap-' . now()->format('Ymd-His') . '.xlsx'
        );
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
