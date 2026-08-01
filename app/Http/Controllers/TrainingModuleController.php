<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingModuleRequest;
use App\Http\Requests\UpdateTrainingModuleRequest;
use App\Models\TrainingModule;
use Illuminate\Database\QueryException;

class TrainingModuleController extends Controller
{
    public function index()
    {
        // Data diambil via DataTables (server-side) lewat route training-modules.data
        return view('training-modules.index');
    }

    /**
     * Endpoint JSON untuk DataTables server-side processing.
     * Dipisah dari index() supaya halaman awal ringan (tidak load semua data sekaligus).
     */
    public function data()
    {
        $modules = TrainingModule::orderBy('name')->get();

        return response()->json(['data' => $modules]);
    }

    public function create()
    {
        return view('training-modules.create');
    }

    public function store(StoreTrainingModuleRequest $request)
    {
        TrainingModule::create($request->validated());

        return redirect()
            ->route('training-modules.index')
            ->with('success', 'Modul training baru berhasil ditambahkan.');
    }

    public function edit(TrainingModule $trainingModule)
    {
        return view('training-modules.edit', compact('trainingModule'));
    }

    public function update(UpdateTrainingModuleRequest $request, TrainingModule $trainingModule)
    {
        $trainingModule->update($request->validated());

        return redirect()
            ->route('training-modules.index')
            ->with('success', 'Modul training berhasil diperbarui. Perubahan ini tidak mengubah riwayat training yang sudah tercatat sebelumnya.');
    }

    /**
     * "Hapus" di sini secara default menonaktifkan modul (is_active = false),
     * bukan menghapus fisik dari database — karena modul yang pernah dipakai
     * di Training Session tidak boleh dihapus (dilindungi FK restrictOnDelete).
     */
    public function destroy(TrainingModule $trainingModule)
    {
        try {
            $trainingModule->delete();

            return redirect()
                ->route('training-modules.index')
                ->with('success', 'Modul training berhasil dihapus.');
        } catch (QueryException $e) {
            $trainingModule->update(['is_active' => false]);

            return redirect()
                ->route('training-modules.index')
                ->with('warning', 'Modul ini sudah pernah dipakai di Training Session sehingga tidak bisa dihapus. Modul otomatis dinonaktifkan saja.');
        }
    }
}
