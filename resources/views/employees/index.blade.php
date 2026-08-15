@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')
@section('page-subtitle', 'Kelola data seluruh karyawan.')

@section('page-actions')
    <a href="{{ route('employees.export.master') }}" class="btn btn-outline-dark">
        <i class="bi bi-download"></i> Export Data Lengkap
    </a>
    <a href="{{ route('employees.import.form') }}" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Import Excel
    </a>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Karyawan
    </a>
@endsection

@section('content')
@if(session('import_failures'))
    <div class="alert alert-warning">
        <strong>Catatan dari proses import:</strong>
        <ul class="mb-0">
            @foreach(session('import_failures') as $failure)
                <li>{{ $failure }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card mb-3">
    <div class="content-card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <select id="filter-department" class="form-select">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter-status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                    <option value="resigned">Resign</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter-type" class="form-select">
                    <option value="">Semua Kategori Pekerja</option>
                    <option value="staff">Staff</option>
                    <option value="dw">Daily Worker</option>
                    <option value="casual">Casual</option>
                    <option value="trainee">Trainee</option>
                    <option value="outsourcing">Outsourcing</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-body">
        <table id="table-employees" class="table table-hover w-100">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Email</th>
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
    const table = $('#table-employees').DataTable({
        ajax: {
            url: '{{ route('employees.data') }}',
            data: function (d) {
                d.department_id = $('#filter-department').val();
                d.employment_status = $('#filter-status').val();
                d.employee_type = $('#filter-type').val();
            }
        },
        columns: [
            { data: 'nik' },
            { data: 'name' },
            { data: 'department' },
            { data: 'position', defaultContent: '-' },
            {
                data: 'employee_type',
                render: (val) => {
                    const map = {
                        staff: 'Staff',
                        dw: 'Daily Worker',
                        casual: 'Casual',
                        trainee: 'Trainee',
                        outsourcing: 'Outsourcing'
                    };
                    return map[val] ?? val;
                }
            },
            {
                data: 'employment_status',
                render: (val) => {
                    const map = {
                        active: '<span class="badge bg-success">Aktif</span>',
                        inactive: '<span class="badge bg-secondary">Nonaktif</span>',
                        resigned: '<span class="badge bg-dark">Resign</span>'
                    };
                    return map[val] ?? val;
                }
            },
            { data: 'email', defaultContent: '-' },
            {
                data: 'id',
                orderable: false,
                render: (id) => `
                    <a href="/employees/${id}" class="btn btn-sm btn-outline-secondary">Detail</a>
                    <a href="/employees/${id}/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="/employees/${id}" method="POST" class="d-inline" onsubmit="return confirm('Nonaktifkan karyawan ini?')">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Nonaktifkan</button>
                    </form>
                `
            }
        ]
    });

    $('#filter-department, #filter-status, #filter-type').on('change', () => table.ajax.reload());
});
</script>
@endpush
