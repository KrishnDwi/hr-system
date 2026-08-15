@csrf

<h6 class="text-primary border-bottom pb-2 mb-3">Identitas & Pekerjaan</h6>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">ID No. / NIK Karyawan <span class="text-danger">*</span></label>
        <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
               value="{{ old('nik', $employee->nik ?? '') }}" required>
        <small class="text-muted">Nomor badge/ID internal — BUKAN NIK KTP</small>
        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $employee->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Departemen <span class="text-danger">*</span></label>
        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
            <option value="">-- Pilih --</option>
            @php $selectedDept = old('department_id', $employee->department_id ?? null); @endphp
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected($selectedDept == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Jabatan</label>
        <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Tanggal Masuk</label>
        <input type="date" name="join_date" class="form-control"
               value="{{ old('join_date', isset($employee->join_date) ? $employee->join_date->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Kategori Pekerja <span class="text-danger">*</span></label>
        <select name="employee_type" class="form-select" required>
            @php $empType = old('employee_type', $employee->employee_type ?? 'staff'); @endphp
            <option value="staff" @selected($empType === 'staff')>Staff (Tetap)</option>
            <option value="dw" @selected($empType === 'dw')>Daily Worker</option>
            <option value="casual" @selected($empType === 'casual')>Casual</option>
            <option value="trainee" @selected($empType === 'trainee')>Trainee</option>
            <option value="outsourcing" @selected($empType === 'outsourcing')>Outsourcing</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Status Karyawan <span class="text-danger">*</span></label>
        <select name="employment_status" class="form-select" required>
            @php $status = old('employment_status', $employee->employment_status ?? 'active'); @endphp
            <option value="active" @selected($status === 'active')>Aktif</option>
            <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            <option value="resigned" @selected($status === 'resigned')>Resign</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">No. Telepon</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone ?? '') }}">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3">Data Pribadi</h6>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">Tempat Lahir</label>
        <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth', $employee->place_of_birth ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal Lahir</label>
        <input type="date" name="date_of_birth" class="form-control"
               value="{{ old('date_of_birth', isset($employee->date_of_birth) ? $employee->date_of_birth->format('Y-m-d') : '') }}">
        @if(isset($employee) && $employee->age)
            <small class="text-muted">Usia saat ini: {{ $employee->age }} tahun (dihitung otomatis)</small>
        @endif
    </div>
    <div class="col-md-4">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            @php $gender = old('gender', $employee->gender ?? ''); @endphp
            <option value="">-- Pilih --</option>
            <option value="male" @selected($gender === 'male')>Laki-laki</option>
            <option value="female" @selected($gender === 'female')>Perempuan</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Agama</label>
        <input type="text" name="religion" class="form-control" value="{{ old('religion', $employee->religion ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Golongan Darah</label>
        <input type="text" name="blood_type" class="form-control" value="{{ old('blood_type', $employee->blood_type ?? '') }}" placeholder="A/B/AB/O">
    </div>
    <div class="col-md-3">
        <label class="form-label">Status Pernikahan (Pajak)</label>
        <input type="text" name="marital_status_tax" class="form-control" value="{{ old('marital_status_tax', $employee->marital_status_tax ?? '') }}" placeholder="TK/0, K/1, dst">
    </div>
    <div class="col-md-2">
        <label class="form-label">Level</label>
        <input type="text" name="job_level" class="form-control" value="{{ old('job_level', $employee->job_level ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Jatah Cuti/Tahun</label>
        <input type="number" name="annual_leave_entitlement" class="form-control" value="{{ old('annual_leave_entitlement', $employee->annual_leave_entitlement ?? '') }}">
    </div>
    <div class="col-md-8">
        <label class="form-label">Alamat</label>
        <textarea name="address" rows="2" class="form-control">{{ old('address', $employee->address ?? '') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Daerah</label>
        <input type="text" name="region" class="form-control" value="{{ old('region', $employee->region ?? '') }}">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3">Data Keluarga & Kontak Darurat</h6>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">Nama Pasangan</label>
        <input type="text" name="spouse_name" class="form-control" value="{{ old('spouse_name', $employee->spouse_name ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Tanggal Lahir Pasangan</label>
        <input type="date" name="spouse_date_of_birth" class="form-control"
               value="{{ old('spouse_date_of_birth', isset($employee->spouse_date_of_birth) ? $employee->spouse_date_of_birth->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Jumlah Anak</label>
        <input type="number" name="children_count" class="form-control" value="{{ old('children_count', $employee->children_count ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Nama Kontak Darurat</label>
        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Hubungan Kontak Darurat</label>
        <input type="text" name="emergency_contact_relationship" class="form-control" value="{{ old('emergency_contact_relationship', $employee->emergency_contact_relationship ?? '') }}" placeholder="Suami/Istri/Orang Tua, dll">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3">Finansial & Legal <span class="badge bg-warning text-dark fw-normal">Data Sensitif</span></h6>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">NPWP</label>
        <input type="text" name="npwp_no" class="form-control" value="{{ old('npwp_no', $employee->npwp_no ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">No. Rekening Bank</label>
        <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $employee->bank_account_number ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Atas Nama Rekening</label>
        <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $employee->bank_account_name ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">NIK KTP</label>
        <input type="text" name="nik_ktp" class="form-control" value="{{ old('nik_ktp', $employee->nik_ktp ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">No. Jamsostek</label>
        <input type="text" name="jamsostek_no" class="form-control" value="{{ old('jamsostek_no', $employee->jamsostek_no ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">No. BPJS</label>
        <input type="text" name="bpjs_no" class="form-control" value="{{ old('bpjs_no', $employee->bpjs_no ?? '') }}">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3">Pendidikan</h6>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Latar Belakang Pendidikan</label>
        <input type="text" name="education_background" class="form-control" value="{{ old('education_background', $employee->education_background ?? '') }}" placeholder="mis. S1 Perhotelan">
    </div>
    <div class="col-md-3">
        <label class="form-label">Jenjang</label>
        <input type="text" name="education_level" class="form-control" value="{{ old('education_level', $employee->education_level ?? '') }}" placeholder="SMA/D3/S1, dst">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3">Akses Portal Karyawan</h6>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Password Login Portal</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="{{ isset($employee) && $employee->password ? 'Sudah punya password — kosongkan jika tidak ingin diubah' : 'Belum punya akses login — isi untuk mengaktifkan' }}">
        <small class="text-muted">Karyawan login pakai <strong>ID No.</strong> + password ini untuk akses Portal Materi Training.</small>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
