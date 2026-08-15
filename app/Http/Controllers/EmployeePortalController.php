<?php

namespace App\Http\Controllers;

use App\Models\TrainingMaterial;
use App\Models\TrainingModule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeePortalController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();

        $modules = TrainingModule::active()
            ->with('materials')
            ->orderBy('name')
            ->get();

        $missingMandatoryModules = $employee->missingMandatoryModules();

        return view('portal.index', compact('employee', 'modules', 'missingMandatoryModules'));
    }

    /**
     * Download materi — WAJIB login (route ini di-guard middleware
     * auth:employee), file disimpan di disk 'local' (privat) sehingga
     * tidak bisa diakses lewat URL langsung tanpa lewat sini.
     */
    public function download(TrainingMaterial $material)
    {
        abort_unless(Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->original_filename);
    }
}
