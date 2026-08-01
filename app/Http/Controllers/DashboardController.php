<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\TrainingHistory;
use App\Models\TrainingModule;
use App\Models\TrainingSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // ==== Kartu ringkasan ====
        $totalEmployees = Employee::active()->count();
        $totalModules = TrainingModule::active()->count();
        $totalSessions = TrainingSession::count();
        $totalMandatoryModules = TrainingModule::active()->mandatory()->count();

        $sessionsThisMonth = TrainingSession::whereMonth('session_date', $today->month)
            ->whereYear('session_date', $today->year)
            ->count();

        $sessionsToday = TrainingSession::whereDate('session_date', $today)->count();

        $expiringSoonCount = TrainingHistory::expiringSoon()->count();
        $expiredCount = TrainingHistory::expired()->count();

        // ==== Mandatory Training Completion ====
        // Basis: (karyawan aktif) x (modul mandatory aktif) = total slot yang wajib dipenuhi.
        // "Terpenuhi" = ada minimal satu riwayat valid (belum expired) untuk pasangan
        // employee x module tersebut.
        $totalRequiredSlots = $totalEmployees * $totalMandatoryModules;

        $completedPairs = TrainingHistory::query()
            ->join('employees', 'training_histories.employee_id', '=', 'employees.id')
            ->where('employees.employment_status', 'active')
            ->where('training_histories.is_mandatory_snapshot', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('training_histories.expired_at')
                    ->orWhere('training_histories.expired_at', '>=', $today->toDateString());
            })
            ->select('training_histories.employee_id', 'training_histories.training_module_id')
            ->distinct()
            ->get()
            ->count();

        $mandatoryCompletionPercentage = $totalRequiredSlots > 0
            ? round(($completedPairs / $totalRequiredSlots) * 100, 1)
            : 0;

        // ==== Statistik Training per Departemen (tahun berjalan) ====
        $statsByDepartment = Department::active()
            ->withCount(['employees as participant_count' => function ($query) use ($today) {
                $query->join('training_participants', 'training_participants.employee_id', '=', 'employees.id')
                    ->join('training_sessions', 'training_sessions.id', '=', 'training_participants.training_session_id')
                    ->whereYear('training_sessions.session_date', $today->year);
            }])
            ->orderBy('name')
            ->get(['id', 'name']);

        // ==== Statistik Training per Bulan (tahun berjalan, Jan–Des) ====
        $sessionsPerMonthRaw = TrainingSession::whereYear('session_date', $today->year)
            ->select(DB::raw("CAST(strftime('%m', session_date) AS INTEGER) as month"), DB::raw('COUNT(*) as total'))
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $sessionsPerMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $sessionsPerMonth[] = $sessionsPerMonthRaw[$m] ?? 0;
        }

        // ==== Daftar mandatory training yang akan/expired (untuk quick-glance table) ====
        $expiringList = TrainingHistory::with('employee')
            ->mandatory()
            ->expiringSoon()
            ->orderBy('expired_at')
            ->limit(10)
            ->get();

        $expiredList = TrainingHistory::with('employee')
            ->mandatory()
            ->expired()
            ->orderByDesc('expired_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalEmployees',
            'totalModules',
            'totalSessions',
            'totalMandatoryModules',
            'sessionsThisMonth',
            'sessionsToday',
            'expiringSoonCount',
            'expiredCount',
            'mandatoryCompletionPercentage',
            'statsByDepartment',
            'monthLabels',
            'sessionsPerMonth',
            'expiringList',
            'expiredList',
        ));
    }
}
