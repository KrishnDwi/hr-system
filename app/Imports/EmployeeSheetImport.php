<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\TrainingModule;
use App\Models\TrainingHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import satu sheet (Staff / DW / Casual / Trainee / Outsourcing).
 *
 * PENTING: sengaja TIDAK pakai WithHeadingRow, karena baris header di file
 * HR asli Anda posisinya BERBEDA-BEDA per sheet (Staff = baris 3, sheet
 * lain = baris 2 — ada baris banner/kosong di atasnya). Sebagai gantinya,
 * kelas ini SCAN beberapa baris pertama untuk menemukan baris yang memuat
 * "ID No." dan "Full Name", baru dari situ membangun peta kolom. Ini bikin
 * import tahan terhadap perbedaan jumlah baris banner/kosong di atas header,
 * tanpa perlu tahu persis di baris ke berapa header berada.
 *
 * Kolom lain dicari dengan fragment matching (case-insensitive, substring)
 * karena penamaan kolom tidak 100% konsisten antar sheet (mis. "Joining
 * Date Hotel" di Staff vs "Join Date" di Casual, "Position" vs
 * "Current Position", dst).
 */
class EmployeeSheetImport implements ToCollection
{
    public array $errors = [];
    public int $processedCount = 0;
    public int $skippedBlankRows = 0;

    public function __construct(protected string $employeeType)
    {
    }

    public function collection(Collection $rows)
    {
        $rows = $rows->values();

        $headerRowIndex = $this->findHeaderRowIndex($rows);

        if ($headerRowIndex === null) {
            $this->errors[] = 'Baris header ("ID No." + "Full Name") tidak ditemukan di sheet ini — sheet dilewati sepenuhnya.';
            return;
        }

        // colIndex => header text (lowercased, trimmed) — dipakai untuk fragment matching
        $headerMap = [];
        foreach ($rows[$headerRowIndex]->toArray() as $colIndex => $cellValue) {
            $text = strtolower(trim((string) $cellValue));
            if ($text !== '') {
                $headerMap[$colIndex] = $text;
            }
        }

        for ($i = $headerRowIndex + 1; $i < $rows->count(); $i++) {
            $rowNumber = $i + 1; // perkiraan nomor baris Excel (cukup untuk penanda di pesan error)
            $rawRow = $rows[$i]->toArray();

            // Susun assoc array: header_text => value, berdasarkan posisi kolom yang sama
            $data = [];
            foreach ($headerMap as $colIndex => $headerText) {
                $data[$headerText] = $rawRow[$colIndex] ?? null;
            }

            // Baris kosong total (banyak terjadi di file asli sebagai pemisah antar departemen) — lewati diam-diam
            if (collect($data)->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                $this->skippedBlankRows++;
                continue;
            }

            try {
                $employee = $this->upsertEmployee($data, $rowNumber);

                if (!$employee) {
                    continue;
                }

                $this->processCertification(
                    data: $data,
                    employee: $employee,
                    moduleCode: 'CERT-FOOD',
                    dateFragments: ['sertifikasi', 'makanan'],
                    expiredFragments: null
                );

                $this->processCertification(
                    data: $data,
                    employee: $employee,
                    moduleCode: 'CERT-KOMP',
                    dateFragments: ['date', 'kompe'], // "Dateserifikasi Kompetensi" — HARUS ada 'date' agar tidak nyasar ke kolom nama LSP/posisi yang juga mengandung "kompeten(si)"
                    expiredFragments: ['expired', 'kompe']
                );

                $this->processContractsFromRow($headerMap, $rawRow, $employee);

                $this->processedCount++;
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
            }
        }
    }

    /**
     * Scan 10 baris pertama untuk menemukan baris yang mengandung
     * "id no" DAN "full name" di antara nilai selnya — itulah baris header.
     */
    protected function findHeaderRowIndex(Collection $rows): ?int
    {
        $limit = min(10, $rows->count());

        for ($i = 0; $i < $limit; $i++) {
            $joined = strtolower(implode(' ', array_map(
                fn ($v) => (string) $v,
                $rows[$i]->toArray()
            )));

            if (str_contains($joined, 'id no') && str_contains($joined, 'full name')) {
                return $i;
            }
        }

        return null;
    }

    protected function upsertEmployee(array $data, int $rowNumber): ?Employee
    {
        // "ID No." dipakai sebagai identifier unik (BUKAN kolom "NIK" asli yang
        // menyimpan Nomor Induk Kependudukan KTP — itu data sensitif yang
        // sengaja tidak kita simpan di ETMS, sesuai kesepakatan sebelumnya).
        $employeeIdNo = $this->findValue($data, ['id', 'no']);
        $name = $this->findValue($data, ['full', 'name']);
        $departmentName = $this->findValue($data, ['department']);

        if (empty($employeeIdNo) || empty($name)) {
            $this->errors[] = "Baris {$rowNumber}: dilewati — kolom ID No. atau Full Name kosong.";
            return null;
        }

        if (empty($departmentName)) {
            $this->errors[] = "Baris {$rowNumber}: dilewati — kolom Department kosong.";
            return null;
        }

        $department = $this->findOrCreateDepartment($departmentName);

        $position = $this->findValue($data, ['current', 'position']) ?? $this->findValue($data, ['position']);
        $joinDate = $this->parseDate($this->findValue($data, ['join', 'date']));

        // "Resign date" terisi adalah sinyal yang jauh lebih akurat ketimbang
        // menebak dari teks "Employee Status" (yang di data Anda ternyata berisi
        // gabungan status+gender, mis. "Permanent Male", "Contract Female").
        $resignDate = $this->parseDate($this->findValue($data, ['resign', 'date']));
        $statusRaw = strtolower((string) $this->findValue($data, ['employee', 'status']));
        $employmentStatus = $resignDate
            ? 'resigned'
            : (str_contains($statusRaw, 'inactive') || str_contains($statusRaw, 'non')
                ? 'inactive'
                : 'active');

        $phone = $this->findValue($data, ['mobile']);
        $email = $this->findValue($data, ['email']);

        $employee = Employee::updateOrCreate(
            ['nik' => trim((string) $employeeIdNo)],
            [
                'name' => trim($name),
                'department_id' => $department->id,
                'position' => $position,
                'join_date' => $joinDate,
                'employment_status' => $employmentStatus,
                'employee_type' => $this->employeeType,
                'email' => $email,
                'phone' => $phone ? (string) $phone : null,

                // Demografi personal
                'place_of_birth' => $this->findExact($data, 'place of birth'),
                'date_of_birth' => $this->parseDate($this->findExact($data, 'date of birth') ?? $this->findValue($data, ['date', 'birth'])),
                'gender' => $this->normalizeGender($this->findValue($data, ['gender'])),
                'religion' => $this->findValue($data, ['religion']),
                'blood_type' => $this->findValue($data, ['darah']), // "Gologan/Golongan Darah"
                'marital_status_tax' => $this->findValue($data, ['status', 'rital']), // cocok utk "Merital"/"Marital Status"
                'job_level' => $this->findExact($data, 'level'),
                'address' => $this->findExact($data, 'address'),
                'region' => $this->findExact($data, 'daerah'),
                'annual_leave_entitlement' => $this->toInt($this->findValue($data, ['entitlement'])),

                // Keluarga
                'spouse_name' => $this->findExact($data, 'spouse'),
                'spouse_date_of_birth' => $this->parseDate($this->findValue($data, ['spouse', 'birth'])),
                'children_count' => $this->toInt($this->findExact($data, 'children')),
                'emergency_contact_name' => $this->findValue($data, ['emergency', 'contact']),
                'emergency_contact_relationship' => $this->findExact($data, 'relationship'),

                // Finansial / legal
                'npwp_no' => $this->findValue($data, ['npwp']),
                'bank_account_number' => $this->findValue($data, ['bca']),
                'bank_account_name' => $this->findExact($data, 'a/n'),
                'nik_ktp' => $this->findExact($data, 'nik'),
                'jamsostek_no' => $this->findValue($data, ['jamsostek']),
                'bpjs_no' => $this->findValue($data, ['bpjs']),

                // Pendidikan
                'education_background' => $this->findValue($data, ['education', 'background']),
                'education_level' => $this->findValue($data, ['level', 'education']),
            ]
        );

        return $employee;
    }

    /**
     * Baca kolom Contract 1-5 (+ End Contract), Jeda, Last Contract, dan
     * Permanent dari Excel, lalu simpan sebagai baris-baris terpisah di
     * employee_contracts (normalisasi — lihat catatan desain di README).
     *
     * PENTING: header "End Contract" MUNCUL BERULANG 5x dengan teks PERSIS
     * SAMA di file asli (satu untuk tiap "Contract N"), jadi tidak bisa
     * dicari berdasarkan nama kolom seperti field lain — harus berdasarkan
     * POSISI kolom (colIndex), yaitu kolom "End Contract" pertama yang
     * muncul SETELAH kolom "Contract N" yang bersangkutan.
     *
     * Dipanggil ulang saat re-import: kontrak lama utk karyawan ini dihapus
     * dulu supaya tidak dobel, lalu ditulis ulang dari Excel.
     */
    protected function processContractsFromRow(array $headerMap, array $rawRow, Employee $employee): void
    {
        // headerText => [colIndex, colIndex, ...] — perlu array karena "End Contract" duplikat
        $colIndexesByHeader = [];
        foreach ($headerMap as $colIndex => $headerText) {
            $colIndexesByHeader[trim($headerText)][] = $colIndex;
        }

        $employee->contracts()->delete();
        $sequence = 0;

        for ($n = 1; $n <= 5; $n++) {
            $startColIndexes = $colIndexesByHeader["contract {$n}"] ?? [];
            if (empty($startColIndexes)) {
                continue;
            }
            $startColIndex = $startColIndexes[0];
            $start = $this->parseDate($rawRow[$startColIndex] ?? null);

            // "End Contract" terdekat yang posisinya SETELAH kolom "Contract N" ini
            $endColIndex = null;
            foreach (($colIndexesByHeader['end contract'] ?? []) as $idx) {
                if ($idx > $startColIndex && ($endColIndex === null || $idx < $endColIndex)) {
                    $endColIndex = $idx;
                }
            }
            $end = $endColIndex !== null ? $this->parseDate($rawRow[$endColIndex] ?? null) : null;

            if (!$start && !$end) {
                continue;
            }

            $employee->contracts()->create([
                'sequence' => ++$sequence,
                'type' => 'contract',
                'start_date' => $start,
                'end_date' => $end,
                'is_last' => false,
            ]);
        }

        $lastStart = $this->exactByHeader($colIndexesByHeader, $rawRow, 'last contract');
        $lastEnd = $this->exactByHeader($colIndexesByHeader, $rawRow, 'end last contract');
        if ($lastStart || $lastEnd) {
            $employee->contracts()->create([
                'sequence' => ++$sequence,
                'type' => 'contract',
                'start_date' => $this->parseDate($lastStart),
                'end_date' => $this->parseDate($lastEnd),
                'is_last' => true,
            ]);
        }

        $permanentDate = $this->exactByHeader($colIndexesByHeader, $rawRow, 'permanen')
            ?? $this->exactByHeader($colIndexesByHeader, $rawRow, 'permanent');
        if ($permanentDate) {
            $employee->contracts()->create([
                'sequence' => ++$sequence,
                'type' => 'permanent',
                'start_date' => $this->parseDate($permanentDate),
                'end_date' => null,
                'is_last' => false,
            ]);
        }
    }

    /**
     * Ambil value kolom pertama yang header-nya PERSIS sama dengan $exactHeader
     * (dari peta colIndexesByHeader yang sudah dibangun) — dipakai untuk
     * kolom yang namanya unik (tidak berulang seperti "End Contract").
     */
    protected function exactByHeader(array $colIndexesByHeader, array $rawRow, string $exactHeader)
    {
        $indexes = $colIndexesByHeader[$exactHeader] ?? [];

        if (empty($indexes)) {
            return null;
        }

        return $rawRow[$indexes[0]] ?? null;
    }

    protected function normalizeGender(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $v = strtolower($value);

        if (str_contains($v, 'female') || $v === 'f' || str_contains($v, 'perempuan')) {
            return 'female';
        }

        if (str_contains($v, 'male') || $v === 'm' || str_contains($v, 'laki')) {
            return 'male';
        }

        return null;
    }

    protected function toInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Cari value berdasarkan HEADER PERSIS (setelah trim+lowercase), bukan
     * fragment — dipakai untuk kolom yang namanya pendek/ambigu (mis. "Level"
     * vs "Level Education Background") supaya tidak salah tangkap.
     */
    protected function findExact(array $data, string $exactHeader): ?string
    {
        foreach ($data as $headerText => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (trim($headerText) === $exactHeader) {
                return (string) $value;
            }
        }

        return null;
    }

    /** @var \Illuminate\Support\Collection|null Cache departemen untuk hindari query berulang per baris */
    protected ?Collection $departmentCache = null;

    /**
     * Cari departemen dengan normalisasi (buang spasi & simbol, lowercase)
     * sebelum membuat baru — supaya "F & B Product" dan "FB Product" (atau
     * "Front office" vs "Front Office") dianggap departemen yang SAMA.
     * Ini terbukti perlu dari data riil Anda: kedua variasi ejaan itu memang
     * muncul di sheet berbeda untuk departemen yang sama.
     *
     * CATATAN: ini hanya menyatukan variasi spasi/simbol/huruf besar-kecil.
     * Nama yang benar-benar berbeda kata (mis. "P&C" vs "Human Resources")
     * TETAP dianggap departemen terpisah — sistem tidak menebak sejauh itu.
     */
    protected function findOrCreateDepartment(string $name): Department
    {
        $trimmed = trim($name);
        $normalizedTarget = $this->normalizeDeptKey($trimmed);

        if ($this->departmentCache === null) {
            $this->departmentCache = Department::all();
        }

        $existing = $this->departmentCache->first(
            fn ($d) => $this->normalizeDeptKey($d->name) === $normalizedTarget
        );

        if ($existing) {
            return $existing;
        }

        $created = Department::create(['name' => $trimmed, 'is_active' => true]);
        $this->departmentCache->push($created);

        return $created;
    }

    protected function normalizeDeptKey(string $name): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
    }

    /**
     * Membuat/memperbarui TrainingHistory dari kolom sertifikasi eksternal.
     * training_participant_id sengaja NULL karena sertifikasi ini didapat
     * di luar Training Session internal (nullable sejak desain Tahap 3).
     */
    protected function processCertification(
        array $data,
        Employee $employee,
        string $moduleCode,
        array $dateFragments,
        ?array $expiredFragments
    ): void {
        $certDate = $this->parseDate($this->findValue($data, $dateFragments));

        if (!$certDate) {
            return; // tidak ada data sertifikasi di baris ini — lewati diam-diam
        }

        $module = TrainingModule::where('code', $moduleCode)->first();

        if (!$module) {
            $this->errors[] = "Modul {$moduleCode} tidak ditemukan di Master Training — jalankan TrainingModuleSeeder terlebih dahulu.";
            return;
        }

        $expiredAt = $expiredFragments
            ? $this->parseDate($this->findValue($data, $expiredFragments))
            : null;

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

    /**
     * Cari value berdasarkan fragment (substring, case-insensitive) di
     * header — bukan nama kolom persis. Contoh: fragments ['current','position']
     * akan cocok dengan header "Current Position".
     */
    protected function findValue(array $data, array $fragments): ?string
    {
        foreach ($data as $headerText => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $matchesAll = true;
            foreach ($fragments as $fragment) {
                if (!str_contains($headerText, $fragment)) {
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
