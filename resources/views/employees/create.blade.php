@extends('layouts.app')

@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan Baru')
@section('page-subtitle', 'Lengkapi data karyawan baru di bawah ini.')

@section('content')
<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('employees.store') }}" method="POST">
            @include('employees._form')
        </form>
    </div>
</div>
@endsection
