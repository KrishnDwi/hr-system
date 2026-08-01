@extends('layouts.app')

@section('title', 'Training Session')
@section('page-title', 'Training Session')
@section('page-subtitle', 'Riwayat dan pembuatan sesi training.')

@section('page-actions')
    <a href="{{ route('training-sessions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Buat Training Session
    </a>
@endsection

@section('content')
<div class="content-card">
    <div class="content-card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Modul Training</th>
                    <th>Trainer</th>
                    <th>Lokasi</th>
                    <th>Jumlah Peserta</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ $session->session_date->format('d M Y') }}</td>
                        <td>
                            {{ $session->trainingModule->name }}
                            @if($session->trainingModule->is_mandatory)
                                <span class="badge bg-danger">Mandatory</span>
                            @endif
                        </td>
                        <td>{{ $session->trainer_name }}</td>
                        <td>{{ $session->location ?? '-' }}</td>
                        <td>{{ $session->participants_count }} orang</td>
                        <td>
                            <a href="{{ route('training-sessions.show', $session) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada training session</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $sessions->links() }}
    </div>
</div>
@endsection
