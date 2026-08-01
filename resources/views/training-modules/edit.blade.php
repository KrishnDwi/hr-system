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

<div class="content-card">
    <div class="content-card-body">
        <form action="{{ route('training-modules.update', $trainingModule) }}" method="POST">
            @method('PUT')
            @include('training-modules._form')
        </form>
    </div>
</div>
@endsection
