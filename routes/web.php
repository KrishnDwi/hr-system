<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingModuleController;

Route::resource('training-sessions', TrainingSessionController::class)
    ->only(['index', 'create', 'store', 'show']);

// PENTING: route 'data' harus didaftarkan SEBELUM Route::resource,
// supaya 'data' tidak tertangkap sebagai {training_module} (route model binding).
Route::get('training-modules/data', [TrainingModuleController::class, 'data'])
    ->name('training-modules.data');

Route::resource('training-modules', TrainingModuleController::class)
    ->except(['show']); // tidak perlu halaman detail terpisah untuk master data sederhana ini

// Data Karyawan
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeContractController;

Route::get('employees/data', [EmployeeController::class, 'data'])
    ->name('employees.data'); // didaftarkan sebelum resource, sama seperti training-modules/data

Route::get('employees/import', [EmployeeController::class, 'showImportForm'])
    ->name('employees.import.form');
Route::post('employees/import', [EmployeeController::class, 'import'])
    ->name('employees.import');

Route::get('employees/export/master', [EmployeeController::class, 'exportMaster'])
    ->name('employees.export.master');

Route::resource('employees', EmployeeController::class);
// Catatan: 'show' TIDAK di-except lagi seperti sebelumnya — sekarang dipakai
// untuk halaman Detail Karyawan (riwayat training + mandatory yang belum/perlu dilakukan).

Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])
    ->name('employees.contracts.store');
Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])
    ->name('employees.contracts.destroy');

// Dashboard
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index']); // arahkan root ke dashboard

// Report
use App\Http\Controllers\ReportController;

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\TrainingMaterialController;

// Login Portal Karyawan (guest — belum login)
Route::middleware('guest:employee')->group(function () {
    Route::get('/portal/login', [EmployeeAuthController::class, 'showLoginForm'])->name('portal.login');
    Route::post('/portal/login', [EmployeeAuthController::class, 'login'])->name('portal.login.submit');
});

// Halaman Portal (WAJIB login sebagai employee)
Route::middleware('auth:employee')->group(function () {
    Route::get('/portal', [EmployeePortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/materials/{material}/download', [EmployeePortalController::class, 'download'])
        ->name('portal.materials.download');
    Route::post('/portal/logout', [EmployeeAuthController::class, 'logout'])->name('portal.logout');
});

// Upload/hapus materi (sisi HR — di halaman Edit Master Training)
Route::post('training-modules/{training_module}/materials', [TrainingMaterialController::class, 'store'])
    ->name('training-modules.materials.store');
Route::delete('training-modules/{training_module}/materials/{material}', [TrainingMaterialController::class, 'destroy'])
    ->name('training-modules.materials.destroy');