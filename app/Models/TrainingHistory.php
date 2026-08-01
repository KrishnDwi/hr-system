<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'training_participant_id',
        'training_module_id',
        'training_code_snapshot',
        'training_name_snapshot',
        'is_mandatory_snapshot',
        'trainer_name_snapshot',
        'training_date',
        'duration_hours_snapshot',
        'validity_months_snapshot',
        'expired_at',
    ];

    protected $casts = [
        'is_mandatory_snapshot' => 'boolean',
        'training_date' => 'date',
        'expired_at' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function trainingParticipant(): BelongsTo
    {
        return $this->belongsTo(TrainingParticipant::class);
    }

    public function trainingModule(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class);
    }

    /**
     * Status dihitung dinamis (bukan kolom statis), agar selalu akurat
     * tanpa perlu cron job untuk sinkronisasi.
     *
     * Nilai: 'no_expiry' | 'valid' | 'expiring_soon' | 'expired'
     */
    public function getStatusAttribute(): string
    {
        if (is_null($this->expired_at)) {
            return 'no_expiry';
        }

        $daysLeft = Carbon::today()->diffInDays($this->expired_at, false);

        if ($daysLeft < 0) {
            return 'expired';
        }

        if ($daysLeft <= 30) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory_snapshot', true);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expired_at')
            ->whereBetween('expired_at', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expired_at')
            ->where('expired_at', '<', now()->toDateString());
    }
}
