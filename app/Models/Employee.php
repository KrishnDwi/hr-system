<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Employee sengaja extends Authenticatable (bukan Model biasa) supaya bisa
 * dipakai untuk login karyawan lewat guard 'employee' TERPISAH dari login
 * HRD (lihat config/auth.php). Ini bukan berarti karyawan otomatis bisa
 * login — hanya yang punya `password` terisi (diset oleh HRD) yang bisa.
 */
class Employee extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nik',
        'name',
        'department_id',
        'position',
        'join_date',
        'employment_status',
        'employee_type',
        'email',
        'phone',
        // Demografi personal
        'place_of_birth',
        'date_of_birth',
        'gender',
        'religion',
        'blood_type',
        'marital_status_tax',
        'job_level',
        'address',
        'region',
        'annual_leave_entitlement',
        // Keluarga
        'spouse_name',
        'spouse_date_of_birth',
        'children_count',
        'emergency_contact_name',
        'emergency_contact_relationship',
        // Finansial / legal
        'npwp_no',
        'bank_account_number',
        'bank_account_name',
        'nik_ktp',
        'jamsostek_no',
        'bpjs_no',
        // Pendidikan
        'education_background',
        'education_level',
        'password',
    ];

    protected $casts = [
        'join_date' => 'date',
        'date_of_birth' => 'date',
        'spouse_date_of_birth' => 'date',
        'password' => 'hashed', // otomatis di-hash saat diisi — HRD tidak perlu Hash::make() manual
    ];

    /**
     * password & remember_token TIDAK boleh ikut ter-serialize (mis. saat
     * employee di-load ke response JSON DataTables) — mencegah kebocoran hash.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function trainingParticipants(): HasMany
    {
        return $this->hasMany(TrainingParticipant::class);
    }

    public function trainingHistories(): HasMany
    {
        return $this->hasMany(TrainingHistory::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class)->orderBy('sequence');
    }

    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('employee_type', $type);
    }

    /**
     * Umur dihitung otomatis dari date_of_birth — SENGAJA tidak disimpan
     * sebagai kolom statis, supaya tidak pernah basi (sama seperti prinsip
     * status expired di TrainingHistory).
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Daftar mandatory training yang PERLU dilakukan/diulang oleh karyawan ini —
     * mencakup yang belum pernah diikuti sama sekali MAUPUN yang riwayatnya
     * sudah expired. Dipakai untuk monitoring mandatory training (FR-07) dan
     * halaman detail karyawan.
     */
    public function missingMandatoryModules()
    {
        $validModuleIds = $this->trainingHistories()
            ->where('is_mandatory_snapshot', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                    ->orWhere('expired_at', '>=', now()->toDateString());
            })
            ->pluck('training_module_id');

        return TrainingModule::mandatory()->active()
            ->whereNotIn('id', $validModuleIds)
            ->get();
    }
}
