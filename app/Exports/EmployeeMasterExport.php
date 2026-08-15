<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export data karyawan LENGKAP — satu baris per karyawan, mencakup seluruh
 * profil HR (bukan hanya riwayat training seperti TrainingHistoryExport).
 *
 * CATATAN KEAMANAN: file ini memuat data finansial/legal sensitif (NPWP,
 * NIK KTP, rekening bank, BPJS). Batasi siapa saja yang boleh mengakses
 * fitur export ini kalau nanti sistem sudah punya role/permission.
 */
class EmployeeMasterExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(protected Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID No.', 'Nama', 'Departemen', 'Jabatan', 'Kategori Pekerja', 'Status',
            'Tanggal Masuk', 'Email', 'No. Telepon',
            'Tempat Lahir', 'Tanggal Lahir', 'Usia', 'Gender', 'Agama', 'Golongan Darah',
            'Status Pernikahan (Pajak)', 'Level', 'Alamat', 'Daerah', 'Jatah Cuti/Tahun',
            'Nama Pasangan', 'Tgl Lahir Pasangan', 'Jumlah Anak', 'Kontak Darurat', 'Hubungan Kontak Darurat',
            'NPWP', 'No. Rekening', 'A/N Rekening', 'NIK KTP', 'No. Jamsostek', 'No. BPJS',
            'Pendidikan', 'Jenjang Pendidikan',
            'Kontrak Terakhir Mulai', 'Kontrak Terakhir Berakhir',
        ];
    }

    public function map($employee): array
    {
        $lastContract = $employee->contracts->firstWhere('is_last', true)
            ?? $employee->contracts->sortByDesc('sequence')->first();

        return [
            $employee->nik,
            $employee->name,
            $employee->department->name,
            $employee->position,
            $employee->employee_type,
            $employee->employment_status,
            $employee->join_date?->format('d/m/Y'),
            $employee->email,
            $employee->phone,

            $employee->place_of_birth,
            $employee->date_of_birth?->format('d/m/Y'),
            $employee->age,
            $employee->gender,
            $employee->religion,
            $employee->blood_type,
            $employee->marital_status_tax,
            $employee->job_level,
            $employee->address,
            $employee->region,
            $employee->annual_leave_entitlement,

            $employee->spouse_name,
            $employee->spouse_date_of_birth?->format('d/m/Y'),
            $employee->children_count,
            $employee->emergency_contact_name,
            $employee->emergency_contact_relationship,

            $employee->npwp_no,
            $employee->bank_account_number,
            $employee->bank_account_name,
            $employee->nik_ktp,
            $employee->jamsostek_no,
            $employee->bpjs_no,

            $employee->education_background,
            $employee->education_level,

            $lastContract?->start_date?->format('d/m/Y'),
            $lastContract?->end_date?->format('d/m/Y'),
        ];
    }
}
