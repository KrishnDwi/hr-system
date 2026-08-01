<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingSessionRequest;
use App\Models\Department;
use App\Models\TrainingModule;
use App\Models\TrainingSession;
use App\Services\TrainingSessionService;

class TrainingSessionController extends Controller
{
    public function __construct(protected TrainingSessionService $trainingSessionService)
    {
    }

    public function index()
    {
        $sessions = TrainingSession::with('trainingModule')
            ->withCount('participants')
            ->latest('session_date')
            ->paginate(20);

        return view('training-sessions.index', compact('sessions'));
    }

    public function create()
    {
        $modules = TrainingModule::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        // Diambil sekaligus (bukan lazy-load AJAX) karena jumlah karyawan aktif
        // di skala hotel (ratusan) masih ringan untuk filter/search di sisi browser.
        $employees = \App\Models\Employee::with('department')
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'nik', 'department_id', 'position']);

        return view('training-sessions.create', compact('modules', 'departments', 'employees'));
    }

    public function store(StoreTrainingSessionRequest $request)
    {
        $validated = $request->validated();
        $employeeIds = $validated['employee_ids'];
        unset($validated['employee_ids']);

        $session = $this->trainingSessionService->createWithParticipants($validated, $employeeIds);

        return redirect()
            ->route('training-sessions.show', $session)
            ->with('success', 'Training session berhasil dibuat dan riwayat peserta otomatis tercatat.');
    }

    public function show(TrainingSession $trainingSession)
    {
        $trainingSession->load('trainingModule', 'participants.employee.department');

        return view('training-sessions.show', compact('trainingSession'));
    }
}
