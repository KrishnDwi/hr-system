<?php

namespace App\Services;

use App\Models\TrainingHistory;
use App\Models\TrainingModule;
use App\Models\TrainingParticipant;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\DB;

class TrainingSessionService
{
    /**
     * Membuat Training Session sekaligus:
     * 1. Menyimpan data session
     * 2. Mendaftarkan peserta (TrainingParticipant)
     * 3. Meng-generate TrainingHistory (snapshot) untuk tiap peserta
     *
     * Ini adalah implementasi dari FR-03 s/d FR-06 — HRD hanya
     * berinteraksi dengan Training Session, sisanya otomatis.
     *
     * @param  array  $sessionData  kolom-kolom untuk training_sessions
     * @param  array  $employeeIds  daftar id karyawan yang mengikuti session ini
     */
    public function createWithParticipants(array $sessionData, array $employeeIds): TrainingSession
    {
        return DB::transaction(function () use ($sessionData, $employeeIds) {
            $session = TrainingSession::create($sessionData);

            $module = TrainingModule::findOrFail($session->training_module_id);

            foreach (array_unique($employeeIds) as $employeeId) {
                $participant = TrainingParticipant::create([
                    'training_session_id' => $session->id,
                    'employee_id' => $employeeId,
                    'attendance_status' => 'present',
                ]);

                $this->generateHistorySnapshot($participant, $session, $module);
            }

            return $session->load('participants.employee', 'trainingModule');
        });
    }

    /**
     * Menambahkan peserta susulan ke session yang sudah ada
     * (misal ada karyawan yang lupa didaftarkan).
     */
    public function addParticipants(TrainingSession $session, array $employeeIds): void
    {
        DB::transaction(function () use ($session, $employeeIds) {
            $module = $session->trainingModule;
            $existingIds = $session->participants()->pluck('employee_id')->all();
            $newIds = array_diff(array_unique($employeeIds), $existingIds);

            foreach ($newIds as $employeeId) {
                $participant = TrainingParticipant::create([
                    'training_session_id' => $session->id,
                    'employee_id' => $employeeId,
                    'attendance_status' => 'present',
                ]);

                $this->generateHistorySnapshot($participant, $session, $module);
            }
        });
    }

    /**
     * Snapshot: menyalin data modul & session apa adanya saat ini,
     * supaya riwayat tidak berubah jika Master Training diedit nanti.
     */
    protected function generateHistorySnapshot(
        TrainingParticipant $participant,
        TrainingSession $session,
        TrainingModule $module
    ): TrainingHistory {
        $expiredAt = $module->validity_months
            ? $session->session_date->copy()->addMonths($module->validity_months)
            : null;

        return TrainingHistory::create([
            'employee_id' => $participant->employee_id,
            'training_participant_id' => $participant->id,
            'training_module_id' => $module->id,
            'training_code_snapshot' => $module->code,
            'training_name_snapshot' => $module->name,
            'is_mandatory_snapshot' => $module->is_mandatory,
            'trainer_name_snapshot' => $session->trainer_name,
            'training_date' => $session->session_date,
            'duration_hours_snapshot' => $session->actual_duration_hours ?? $module->standard_duration_hours,
            'validity_months_snapshot' => $module->validity_months,
            'expired_at' => $expiredAt,
        ]);
    }
}
