<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Karyawan') — Harris Hotel Seminyak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f3f4f6; }
        .navbar-portal { background-color: #111827; }
        .content-card {
            background: #fff; border-radius: 16px; border: 1px solid #eef0f2;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .content-card .content-card-header { padding: 18px 24px; border-bottom: 1px solid #f1f2f4; font-weight: 700; color: #111827; }
        .content-card .content-card-body { padding: 24px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-portal">
    <div class="container">
        <span class="navbar-brand fw-bold">Portal Karyawan — Harris Hotel Seminyak</span>
        @auth('employee')
            <div class="d-flex align-items-center gap-3">
                <span class="text-light small">{{ auth('employee')->user()->name }}</span>
                <form action="{{ route('portal.logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Keluar</button>
                </form>
            </div>
        @endauth
    </div>
</nav>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
