@extends('layouts.app')

@section('title', 'Import Data Karyawan')
@section('page-title', 'Import Data Karyawan')
@section('page-subtitle', 'Upload file Excel/CSV untuk menambah atau memperbarui data karyawan secara massal.')

@section('content')
<div class="content-card">
    <div class="content-card-body">
        <div class="alert alert-info">
            <strong>Upload file Excel HR Anda apa adanya</strong> — sistem akan otomatis membaca
            sheet berikut (sheet lain akan diabaikan):
            <code>Staff</code>, <code>DW</code>, <code>Casual</code>, <code>Training</code>, <code>Outsourcing</code>.

            <ul class="mb-0 mt-2">
                <li>Kolom yang diambil dari tiap sheet: <strong>ID No./NIK</strong>, <strong>Full Name</strong>,
                    <strong>Department</strong>, <strong>Current Position</strong>, <strong>Joining Date Hotel</strong>,
                    <strong>Employee Status</strong>, <strong>Mobile/HP Number</strong>, <strong>Email</strong>.
                    Kolom lain (NPWP, BPJS, rekening, kontrak, data keluarga, dll) <strong>tidak diimpor</strong>
                    karena di luar cakupan ETMS.</li>
                <li>Kolom <strong>Sertifikasi Penjamah Makanan</strong> dan <strong>Sertifikasi Kompetensi</strong>
                    (+ tanggal/expired-nya jika ada) otomatis dijadikan <strong>riwayat training</strong>
                    di sistem — bukan sekadar kolom data karyawan.</li>
                <li>Kategori pekerja (Staff/DW/Casual/Trainee/Outsourcing) otomatis ditentukan dari
                    nama sheet asal data.</li>
                <li>Import berdasarkan <strong>NIK/ID No.</strong> — data yang sudah ada akan di-<strong>update</strong>,
                    bukan duplikat.</li>
            </ul>
        </div>

        <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">File Excel HR <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                       accept=".xlsx,.xls,.csv" required>
                @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-upload"></i> Import Sekarang
            </button>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
