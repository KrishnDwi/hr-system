<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\TrainingHistory;
use App\Models\TrainingModule;
use App\Models\TrainingSession;
use Illuminate\Support\Carbon;

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
        // Sengaja TIDAK pakai raw SQL (mis. MONTH() ala MySQL) karena tidak
        // portable ke SQLite (driver default project ini). whereYear() adalah
        // method bawaan Laravel yang otomatis diterjemahkan ke sintaks yang
        // benar sesuai driver aktif (MySQL/SQLite/Postgres) — pengelompokan
        // per bulan dilakukan di PHP supaya tidak bergantung ke fungsi SQL
        // spesifik database tertentu.
        $sessionsPerMonthRaw = TrainingSession::whereYear('session_date', $today->year)
            ->get(['session_date'])
            ->groupBy(fn ($session) => $session->session_date->month)
            ->map->count();

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $sessionsPerMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $sessionsPerMonth[] = $sessionsPerMonthRaw[$m] ?? 0;
        }

        // ==== Pengingat: Training apa saja yang perlu dijadwalkan TAHUN INI ====
        // Dikelompokkan per Modul Training (bukan per baris karyawan) — supaya HRD
        // langsung tahu "training apa yang perlu diadakan", bukan sekadar daftar
        // nama yang expired satu-satu.
        //
        // Definisi "perlu tahun ini": karyawan aktif yang TIDAK punya riwayat modul
        // tsb yang validitasnya bertahan sampai akhir tahun ini. Ini mencakup 3 kasus
        // sekaligus: belum pernah ikut sama sekali, sudah expired, atau akan expired
        // sebelum 31 Desember tahun ini (meski saat ini masih valid).
        $yearEnd = $today->copy()->endOfYear()->toDateString();

        $trainingsNeededThisYear = TrainingModule::active()
            ->mandatory()
            ->get()
            ->map(function ($module) use ($yearEnd) {
                $employeeIdsCoveredPastYearEnd = TrainingHistory::where('training_module_id', $module->id)
                    ->where(function ($q) use ($yearEnd) {
                        $q->whereNull('expired_at')->orWhere('expired_at', '>', $yearEnd);
                    })
                    ->pluck('employee_id')
                    ->unique();

                $needCount = Employee::active()
                    ->whereNotIn('id', $employeeIdsCoveredPastYearEnd)
                    ->count();

                $sessionsThisYearForModule = TrainingSession::where('training_module_id', $module->id)
                    ->whereYear('session_date', now()->year)
                    ->count();

                return (object) [
                    'module' => $module,
                    'need_count' => $needCount,
                    'sessions_this_year' => $sessionsThisYearForModule,
                ];
            })
            ->filter(fn ($item) => $item->need_count > 0)
            ->sortByDesc('need_count')
            ->values();

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
            'trainingsNeededThisYear',
        ));
    }
}
