<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Entry point import Excel Data Karyawan.
 *
 * Memetakan nama sheet ke kategori pekerja (employee_type). Sheet yang tidak
 * dikenali otomatis diabaikan oleh Laravel Excel (tidak error), jadi aman
 * kalau workbook Anda punya sheet tambahan lain di luar 5 ini.
 *
 * Asumsi pemetaan (silakan koreksi kalau ada yang keliru):
 * - Staff       -> staff (karyawan tetap)
 * - DW          -> dw (Daily Worker)
 * - Casual      -> casual
 * - Training    -> trainee (asumsi: sheet ini berisi karyawan trainee,
 *                 BUKAN data training/riwayat — karena riwayat training
 *                 sudah ditangani terpisah oleh Training Session di ETMS)
 * - Outsourcing -> outsourcing
 */
class EmployeesImport implements WithMultipleSheets
{
    /** @var EmployeeSheetImport[] */
    protected array $sheetImports = [];

    protected array $sheetTypeMap = [
        'Staff' => 'staff',
        'DW' => 'dw',
        'Casual' => 'casual',
        'Training' => 'trainee',
        'Outsourcing' => 'outsourcing',
    ];

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->sheetTypeMap as $sheetName => $employeeType) {
            $import = new EmployeeSheetImport($employeeType);
            $this->sheetImports[$sheetName] = $import;
            $sheets[$sheetName] = $import;
        }

        return $sheets;
    }

    /**
     * Kumpulan error dari semua sheet yang berhasil diproses (dipakai
     * controller untuk menampilkan balik ke HRD).
     */
    public function allErrors(): array
    {
        $errors = [];

        foreach ($this->sheetImports as $sheetName => $import) {
            foreach ($import->errors as $error) {
                $errors[] = "[Sheet {$sheetName}] {$error}";
            }
        }

        return $errors;
    }

    public function totalProcessed(): int
    {
        return array_sum(array_map(fn ($import) => $import->processedCount, $this->sheetImports));
    }
}
