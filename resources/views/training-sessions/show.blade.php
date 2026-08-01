@extends('layouts.app')

@section('title', 'Detail Training Session')
@section('page-title', 'Detail Training Session')
@section('page-subtitle', $trainingSession->trainingModule->name)

@section('page-actions')
    <a href="{{ route('training-sessions.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
<div class="content-card mb-3">
    <div class="content-card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Modul Training:</strong>
                    {{ $trainingSession->trainingModule->name }}
                    @if($trainingSession->trainingModule->is_mandatory)
                        <span class="badge bg-danger">Mandatory</span>
                    @endif
                </p>
                <p><strong>Trainer:</strong> {{ $trainingSession->trainer_name }}</p>
                <p><strong>Tanggal:</strong> {{ $trainingSession->session_date->format('d M Y') }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Jam:</strong> {{ $trainingSession->start_time }} – {{ $trainingSession->end_time }}</p>
                <p><strong>Lokasi:</strong> {{ $trainingSession->location ?? '-' }}</p>
                <p class="mb-0"><strong>Catatan:</strong> {{ $trainingSession->notes ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">
        Peserta Training ({{ $trainingSession->participants->count() }} orang)
    </div>
    <div class="content-card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="ps-4">NIK</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th class="pe-4">Status Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trainingSession->participants as $participant)
                    <tr>
                        <td class="ps-4">{{ $participant->employee->nik }}</td>
                        <td>{{ $participant->employee->name }}</td>
                        <td>{{ $participant->employee->department->name }}</td>
                        <td class="pe-4">
                            @php
                                $statusMap = [
                                    'present' => 'bg-success',
                                    'absent' => 'bg-danger',
                                    'excused' => 'bg-warning',
                                ];
                            @endphp
                            <span class="badge {{ $statusMap[$participant->attendance_status] }}">
                                {{ ucfirst($participant->attendance_status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
