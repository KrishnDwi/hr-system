@csrf

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Kode Training <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $trainingModule->code ?? '') }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Nama Training <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $trainingModule->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Kategori</label>
        <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
               value="{{ old('category', $trainingModule->category ?? '') }}">
        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Mandatory? <span class="text-danger">*</span></label>
        <select name="is_mandatory" class="form-select @error('is_mandatory') is-invalid @enderror" required>
            @php $isMandatory = old('is_mandatory', $trainingModule->is_mandatory ?? false); @endphp
            <option value="1" @selected($isMandatory)>Ya, Mandatory</option>
            <option value="0" @selected(!$isMandatory)>Tidak, Non Mandatory</option>
        </select>
        @error('is_mandatory') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Durasi Standar (jam)</label>
        <input type="number" step="0.5" name="standard_duration_hours"
               class="form-control @error('standard_duration_hours') is-invalid @enderror"
               value="{{ old('standard_duration_hours', $trainingModule->standard_duration_hours ?? '') }}">
        @error('standard_duration_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Masa Berlaku (bulan)</label>
        <input type="number" name="validity_months"
               class="form-control @error('validity_months') is-invalid @enderror"
               value="{{ old('validity_months', $trainingModule->validity_months ?? '') }}"
               placeholder="Kosongkan jika tidak ada masa berlaku">
        @error('validity_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
            @php $isActive = old('is_active', $trainingModule->is_active ?? true); @endphp
            <option value="1" @selected($isActive)>Aktif</option>
            <option value="0" @selected(!$isActive)>Nonaktif</option>
        </select>
        @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $trainingModule->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('training-modules.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
