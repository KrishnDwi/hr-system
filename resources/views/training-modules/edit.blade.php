@extends('layouts.app')

@section('title', 'Edit Modul Training')
@section('page-title', 'Edit Modul Training')
@section('page-subtitle', $trainingModule->name)

@section('content')
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Perubahan di sini (misalnya status mandatory atau masa berlaku) <strong>tidak akan mengubah
    riwayat training yang sudah tercatat sebelumnya</strong> — riwayat lama tersimpan sebagai
    snapshot terpisah agar laporan historis tetap akurat.
</div>

<div class="content-card mb-3">
    <div class="content-card-body">
        <form action="{{ route('training-modules.update', $trainingModule) }}" method="POST">
            @method('PUT')
            @include('training-modules._form')
        </form>
    </div>
</div>

{{-- ===== Materi Training (diakses & didownload karyawan lewat Portal) ===== --}}
<div class="content-card">
    <div class="content-card-header d-flex justify-content-between align-items-center">
        <span>Materi Training</span>
        <span class="badge bg-secondary">{{ $trainingModule->materials->count() }} file</span>
    </div>
    <div class="content-card-body">
        <p class="text-muted small">
            File di sini bisa diakses & didownload karyawan lewat Portal Karyawan
            (perlu login). Mendukung semua jenis file (PDF, PPT, Word, video, dll), maks 50MB.
        </p>

        <table class="table table-sm mb-3">
            <thead><tr><th>Judul</th><th>Nama File</th><th>Ukuran</th><th>Diupload</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($trainingModule->materials as $material)
                    <tr>
                        <td>{{ $material->title }}</td>
                        <td>{{ $material->original_filename }}</td>
                        <td>{{ $material->formatted_size }}</td>
                        <td>{{ $material->created_at->format('d M Y') }}</td>
                        <td>
                            <form action="{{ route('training-modules.materials.destroy', [$trainingModule, $material]) }}"
                                  method="POST" onsubmit="return confirm('Hapus materi ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada materi diupload</td></tr>
                @endforelse
            </tbody>
        </table>

        <form action="{{ route('training-modules.materials.store', $trainingModule) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small">Judul Materi</label>
                <input type="text" name="title" class="form-control form-control-sm" required placeholder="mis. Slide Presentasi Fire Safety">
            </div>
            <div class="col-md-5">
                <label class="form-label small">File</label>
                <input type="file" name="file" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Upload Materi</button>
            </div>
        </form>
    </div>
</div>
@endsection
