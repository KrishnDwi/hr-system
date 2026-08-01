<?php

namespace App\Http\Controllers;

use App\Exports\TrainingHistoryExport;
use App\Models\Department;
use App\Models\TrainingHistory;
use App\Models\TrainingModule;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::active()->orderBy('name')->get();
        $modules = TrainingModule::orderBy('name')->get();

        $histories = $this->buildQuery($request)->paginate(30)->withQueryString();

        // Ringkasan cepat sesuai hasil filter yang sedang aktif
        $summary = [
            'total' => (clone $this->buildQuery($request))->count(),
            'mandatory' => (clone $this->buildQuery($request))->where('is_mandatory_snapshot', true)->count(),
            'expired' => (clone $this->buildQuery($request))->expired()->count(),
            'expiring_soon' => (clone $this->buildQuery($request))->expiringSoon()->count(),
        ];

        return view('reports.index', compact('histories', 'departments', 'modules', 'summary'));
    }

    public function exportExcel(Request $request)
    {
        $query = $this->buildQuery($request);

        return Excel::download(
            new TrainingHistoryExport($query),
            'laporan-training-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $histories = $this->buildQuery($request)->get();

        $pdf = Pdf::loadView('reports.pdf', compact('histories'))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-training-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Query dasar yang dipakai bersama oleh halaman index, export Excel, dan export PDF —
     * supaya filter yang diterapkan HRD selalu konsisten di ketiga tempat tersebut.
     */
    protected function buildQuery(Request $request)
    {
        $query = TrainingHistory::query()
            ->with(['employee.department', 'trainingModule'])
            ->orderByDesc('training_date');

        if ($request->filled('date_from')) {
            $query->whereDate('training_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('training_date', '<=', $request->date_to);
        }

        if ($request->filled('employee_name')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->employee_name . '%');
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('position')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('position', 'like', '%' . $request->position . '%');
            });
        }

        if ($request->filled('training_module_id')) {
            $query->where('training_module_id', $request->training_module_id);
        }

        if ($request->filled('mandatory') && $request->mandatory !== 'all') {
            $query->where('is_mandatory_snapshot', $request->mandatory === 'mandatory');
        }

        return $query;
    }
}
