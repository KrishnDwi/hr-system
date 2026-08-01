@extends('layouts.app')

@section('title', 'Master Training')
@section('page-title', 'Master Training')
@section('page-subtitle', 'Kelola seluruh modul training perusahaan.')

@section('page-actions')
    <a href="{{ route('training-modules.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Modul Training
    </a>
@endsection

@section('content')
<div class="content-card">
    <div class="content-card-body">
        <table id="table-modules" class="table table-hover w-100">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Training</th>
                    <th>Kategori</th>
                    <th>Mandatory</th>
                    <th>Masa Berlaku</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#table-modules').DataTable({
        ajax: '{{ route('training-modules.data') }}',
        columns: [
            { data: 'code' },
            { data: 'name' },
            { data: 'category', defaultContent: '-' },
            {
                data: 'is_mandatory',
                render: (val) => val
                    ? '<span class="badge bg-danger">Mandatory</span>'
                    : '<span class="badge bg-secondary">Non Mandatory</span>'
            },
            {
                data: 'validity_months',
                render: (val) => val ? val + ' bulan' : '-'
            },
            {
                data: 'is_active',
                render: (val) => val
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>'
            },
            {
                data: 'id',
                orderable: false,
                render: (id) => `
                    <a href="/training-modules/${id}/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="/training-modules/${id}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus/nonaktifkan modul ini?')">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                `
            }
        ]
    });
});
</script>
@endpush
