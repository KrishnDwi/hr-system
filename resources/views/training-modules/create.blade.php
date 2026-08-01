@extends('layouts.app')

@section('title', 'Tambah Modul Training')
@section('page-title', 'Tambah Modul Training Baru')
@section('page-subtitle', 'Buat modul training baru yang akan tampil di daftar Master Training.')

@section('content')
<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('training-modules.store') }}" method="POST">
            @include('training-modules._form')
        </form>
    </div>
</div>
@endsection
