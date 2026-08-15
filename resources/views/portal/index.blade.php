@extends('layouts.portal')

@section('title', 'Materi Training')

@section('content')
<h4 class="mb-1">Halo, {{ $employee->name }} 👋</h4>
<p class="text-muted mb-4">{{ $employee->department->name }} — {{ $employee->position ?? '' }}</p>

@if($missingMandatoryModules->isNotEmpty())
    <div class="content-card mb-4" style="border-left: 4px solid #dc2626;">
        <div class="content-card-header" style="color:#b91c1c;">
            Mandatory Training yang Perlu Anda Lakukan/Ulangi
        </div>
        <div class="content-card-body">
            <ul class="mb-0">
                @foreach($missingMandatoryModules as $module)
                    <li>{{ $module->name }}</li>
                @endforeach
            </ul>
            <small class="text-muted d-block mt-2">Hubungi HRD untuk penjadwalan training ini.</small>
        </div>
    </div>
@endif

<h5 class="mb-3">Materi Training Tersedia</h5>

<div class="row g-3">
    @forelse($modules as $module)
        <div class="col-md-6">
            <div class="content-card h-100">
                <div class="content-card-header d-flex justify-content-between align-items-start">
                    <span>{{ $module->name }}</span>
                    @if($module->is_mandatory)
                        <span class="badge bg-danger">Mandatory</span>
                    @endif
                </div>
                <div class="content-card-body">
                    @if($module->materials->isEmpty())
                        <p class="text-muted small mb-0">Belum ada materi diupload untuk modul ini.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($module->materials as $material)
                                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <div class="fw-semibold">{{ $material->title }}</div>
                                        <small class="text-muted">{{ $material->original_filename }} · {{ $material->formatted_size }}</small>
                                    </div>
                                    <a href="{{ route('portal.materials.download', $material) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <p class="text-muted">Belum ada modul training aktif.</p>
        </div>
    @endforelse
</div>
@endsection
