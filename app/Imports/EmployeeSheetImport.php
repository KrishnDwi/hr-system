<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\TrainingModule;
use App\Models\TrainingHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import satu sheet (Staff / DW / Casual / Trainee / Outsourcing).
 *
 * Dipakai TIDAK LANGSUNG oleh user — dipanggil oleh EmployeesImport
 * (WithMultipleSheets) yang menentukan $employeeType per sheet.
 *
 * Pakai ToCollection (bukan ToModel) karena satu baris Excel bisa
 * menghasilkan LEBIH DARI SATU record: 1 Employee + hingga 2 TrainingHistory
 * (sertifikasi penjamah makanan & kompetensi) — sesuatu yang tidak bisa
 * dilakukan ToModel yang hanya mengembalikan satu model per baris.
 *
 * Kolom dicari dengan fragment matching (case-insensitive, bukan nama kolom
 * persis) karena header Excel HR asli tidak selalu konsisten penulisannya
 * (contoh: "Sertifikasi Kompentensi" vs "Kompetensi", "Dateserifikasi...").
 */
class EmployeeSheetImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $processedCount = 0;

    public function __construct(protected string $employeeType)
    {
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2: baris 1 = header, index dimulai dari 0

            try {
                $employee = $this->upsertEmployee($row, $rowNumber);

                if (!$employee) {
                    continue;
                }

                $this->processCertification(
                    row: $row,
                    employee: $employee,
                    moduleCode: 'CERT-FOOD',
                    dateFragments: ['sertifikasi', 'makanan'],
                    expiredFragments: null
                );

                $this->processCertification(
                    row: $row,
                    employee: $employee,
                    moduleCode: 'CERT-KOMP',
                    dateFragments: ['kompeten'],
                    expiredFragments: ['expired', 'kompeten']
                );

                $this->processedCount++;
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
            }
        }
    }

    protected function upsertEmployee(Collection $row, int $rowNumber): ?Employee
    {
        $nik = $this->findValue($row, ['id', 'no']) ?? $this->findValue($row, ['nik']);
        $name = $this->findValue($row, ['full', 'name']) ?? $this->findValue($row, ['name']);
        $departmentName = $this->findValue($row, ['department']);

        if (empty($nik) || empty($name)) {
            $this->errors[] = "Baris {$rowNumber}: dilewati — kolom ID No./NIK atau Nama kosong.";
            return null;
        }

        if (empty($departmentName)) {
            $this->errors[] = "Baris {$rowNumber}: dilewati — kolom Department kosong.";
            return null;
        }

        $department = Department::firstOrCreate(
            ['name' => trim($departmentName)],
            ['is_active' => true]
        );

        $position = $this->findValue($row, ['current', 'position']) ?? $this->findValue($row, ['position']);
        $joinDate = $this->parseDate(
            $this->findValue($row, ['joining', 'date', 'hotel']) ?? $this->findValue($row, ['joining', 'date'])
        );
        $statusRaw = strtolower((string) $this->findValue($row, ['employee', 'status']));
        $employmentStatus = str_contains($statusRaw, 'resign')
            ? 'resigned'
            : (str_contains($statusRaw, 'inactive') || str_contains($statusRaw, 'non')
                ? 'inactive'
                : 'active');

        $phone = $this->findValue($row, ['mobile']) ?? $this->findValue($row, ['hp', 'number']);
        $email = $this->findValue($row, ['email']);

        return Employee::updateOrCreate(
            ['nik' => trim((string) $nik)],
            [
                'name' => trim($name),
                'department_id' => $department->id,
                'position' => $position,
                'join_date' => $joinDate,
                'employment_status' => $employmentStatus,
                'employee_type' => $this->employeeType,
                'email' => $email,
                'phone' => $phone ? (string) $phone : null,
            ]
        );
    }

    /**
     * Membuat/memperbarui TrainingHistory dari kolom sertifikasi eksternal
     * di Excel. training_participant_id sengaja NULL karena sertifikasi ini
     * didapat di luar sistem Training Session internal (lihat desain
     * Tahap 3 — field ini memang dibuat nullable untuk kasus seperti ini).
     */
    protected function processCertification(
        Collection $row,
        Employee $employee,
        string $moduleCode,
        array $dateFragments,
        ?array $expiredFragments
    ): void {
        $certDate = $this->parseDate($this->findValue($row, $dateFragments));

        if (!$certDate) {
            return; // tidak ada data sertifikasi di baris ini, lewati diam-diam
        }

        $module = TrainingModule::where('code', $moduleCode)->first();

        if (!$module) {
            $this->errors[] = "Modul {$moduleCode} tidak ditemukan di Master Training — jalankan TrainingModuleSeeder terlebih dahulu.";
            return;
        }

        $expiredAt = $expiredFragments
            ? $this->parseDate($this->findValue($row, $expiredFragments))
            : null;

        // Kalau kolom expired tidak ada di Excel, hitung dari masa berlaku modul.
        if (!$expiredAt && $module->validity_months) {
            $expiredAt = Carbon::parse($certDate)->addMonths($module->validity_months)->format('Y-m-d');
        }

        TrainingHistory::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'training_module_id' => $module->id,
                'training_date' => $certDate,
            ],
            [
                'training_participant_id' => null,
                'training_code_snapshot' => $module->code,
                'training_name_snapshot' => $module->name,
                'is_mandatory_snapshot' => $module->is_mandatory,
                'trainer_name_snapshot' => 'Sertifikasi Eksternal (Import Excel)',
                'duration_hours_snapshot' => $module->standard_duration_hours,
                'validity_months_snapshot' => $module->validity_months,
                'expired_at' => $expiredAt,
            ]
        );
    }

    protected function findValue(Collection $row, array $fragments): ?string
    {
        foreach ($row as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $normalizedKey = strtolower((string) $key);
            $matchesAll = true;

            foreach ($fragments as $fragment) {
                if (!str_contains($normalizedKey, $fragment)) {
                    $matchesAll = false;
                    break;
                }
            }

            if ($matchesAll) {
                return (string) $value;
            }
        }

        return null;
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
