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

Route::get('employees/data', [EmployeeController::class, 'data'])
    ->name('employees.data'); // didaftarkan sebelum resource, sama seperti training-modules/data

Route::get('employees/import', [EmployeeController::class, 'showImportForm'])
    ->name('employees.import.form');
Route::post('employees/import', [EmployeeController::class, 'import'])
    ->name('employees.import');

Route::resource('employees', EmployeeController::class)
    ->except(['show']);

// Dashboard
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index']); // arahkan root ke dashboard

// Report
use App\Http\Controllers\ReportController;

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
