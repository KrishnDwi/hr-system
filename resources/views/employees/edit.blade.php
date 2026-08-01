@extends('layouts.app')

@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Karyawan')
@section('page-subtitle', $employee->name)

@section('content')
<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('employees.update', $employee) }}" method="POST">
            @method('PUT')
            @include('employees._form')
        </form>
    </div>
</div>
@endsection
