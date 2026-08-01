<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TrainingHistoryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
            'NIK',
            'Nama Karyawan',
            'Departemen',
            'Jabatan',
            'Kode Training',
            'Nama Training',
            'Mandatory',
            'Tanggal Training',
            'Trainer',
            'Durasi (jam)',
            'Tanggal Expired',
            'Status',
        ];
    }

    public function map($history): array
    {
        return [
            $history->employee->nik,
            $history->employee->name,
            $history->employee->department->name,
            $history->employee->position,
            $history->training_code_snapshot,
            $history->training_name_snapshot,
            $history->is_mandatory_snapshot ? 'Ya' : 'Tidak',
            $history->training_date->format('d/m/Y'),
            $history->trainer_name_snapshot,
            $history->duration_hours_snapshot,
            $history->expired_at?->format('d/m/Y') ?? '-',
            match ($history->status) {
                'expired' => 'Expired',
                'expiring_soon' => 'Akan Expired',
                'valid' => 'Valid',
                default => 'Tidak Ada Masa Berlaku',
            },
        ];
    }
}
