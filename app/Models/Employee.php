<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
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
    ];

    protected $casts = [
        'join_date' => 'date',
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

    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('employee_type', $type);
    }

    /**
     * Daftar mandatory training yang BELUM pernah diikuti sama sekali oleh karyawan ini.
     * Dipakai untuk monitoring mandatory training (FR-07).
     */
    public function missingMandatoryModules()
    {
        $takenModuleIds = $this->trainingHistories()
            ->where('is_mandatory_snapshot', true)
            ->pluck('training_module_id');

        return TrainingModule::mandatory()->active()
            ->whereNotIn('id', $takenModuleIds)
            ->get();
    }
}
