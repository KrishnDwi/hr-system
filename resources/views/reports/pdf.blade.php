<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #eee; }
        .badge-mandatory { color: #b00000; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Laporan Riwayat Training</h2>
    <div class="meta">Dicetak pada: {{ now()->format('d M Y H:i') }} — Total baris: {{ $histories->count() }}</div>

    <table>
        <thead>
            <tr>
                <th>NIK</th>
                <th>Nama</th>
                <th>Departemen</th>
                <th>Training</th>
                <th>Mandatory</th>
                <th>Tanggal</th>
                <th>Trainer</th>
                <th>Expired</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histories as $history)
                <tr>
                    <td>{{ $history->employee->nik }}</td>
                    <td>{{ $history->employee->name }}</td>
                    <td>{{ $history->employee->department->name }}</td>
                    <td>{{ $history->training_name_snapshot }}</td>
                    <td class="{{ $history->is_mandatory_snapshot ? 'badge-mandatory' : '' }}">
                        {{ $history->is_mandatory_snapshot ? 'Ya' : 'Tidak' }}
                    </td>
                    <td>{{ $history->training_date->format('d/m/Y') }}</td>
                    <td>{{ $history->trainer_name_snapshot }}</td>
                    <td>{{ $history->expired_at?->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
