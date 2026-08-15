@extends('layouts.portal')

@section('title', 'Login Portal Karyawan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="content-card mt-5">
            <div class="content-card-body">
                <h4 class="mb-1 text-center">Portal Karyawan</h4>
                <p class="text-muted text-center mb-4">Akses materi training</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('portal.login.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">ID No.</label>
                        <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>

                <p class="text-muted text-center small mt-3 mb-0">
                    Belum punya akses login? Hubungi HRD.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
