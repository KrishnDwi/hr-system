<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Http\Request;

class EmployeeContractController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:permanent,contract,jeda'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_last' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $nextSequence = ($employee->contracts()->max('sequence') ?? 0) + 1;

        // Kalau kontrak baru ditandai "terakhir", lepas tanda itu dari kontrak lain
        if ($request->boolean('is_last')) {
            $employee->contracts()->update(['is_last' => false]);
        }

        $employee->contracts()->create([
            ...$validated,
            'sequence' => $nextSequence,
            'is_last' => $request->boolean('is_last'),
        ]);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Riwayat kontrak berhasil ditambahkan.');
    }

    public function destroy(Employee $employee, EmployeeContract $contract)
    {
        abort_unless($contract->employee_id === $employee->id, 404);

        $contract->delete();

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Riwayat kontrak berhasil dihapus.');
    }
}
