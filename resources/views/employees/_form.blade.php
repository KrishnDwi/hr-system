@csrf

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">NIK <span class="text-danger">*</span></label>
        <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
               value="{{ old('nik', $employee->nik ?? '') }}" required>
        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $employee->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Departemen <span class="text-danger">*</span></label>
        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
            <option value="">-- Pilih Departemen --</option>
            @php $selectedDept = old('department_id', $employee->department_id ?? null); @endphp
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected($selectedDept == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Jabatan</label>
        <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
               value="{{ old('position', $employee->position ?? '') }}">
        @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Tanggal Masuk</label>
        <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror"
               value="{{ old('join_date', isset($employee->join_date) ? $employee->join_date->format('Y-m-d') : '') }}">
        @error('join_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Kategori Pekerja <span class="text-danger">*</span></label>
        <select name="employee_type" class="form-select @error('employee_type') is-invalid @enderror" required>
            @php $empType = old('employee_type', $employee->employee_type ?? 'staff'); @endphp
            <option value="staff" @selected($empType === 'staff')>Staff (Tetap)</option>
            <option value="dw" @selected($empType === 'dw')>Daily Worker</option>
            <option value="casual" @selected($empType === 'casual')>Casual</option>
            <option value="trainee" @selected($empType === 'trainee')>Trainee</option>
            <option value="outsourcing" @selected($empType === 'outsourcing')>Outsourcing</option>
        </select>
        @error('employee_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status Karyawan <span class="text-danger">*</span></label>
        <select name="employment_status" class="form-select @error('employment_status') is-invalid @enderror" required>
            @php $status = old('employment_status', $employee->employment_status ?? 'active'); @endphp
            <option value="active" @selected($status === 'active')>Aktif</option>
            <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            <option value="resigned" @selected($status === 'resigned')>Resign</option>
        </select>
        @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $employee->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">No. Telepon</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $employee->phone ?? '') }}">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
