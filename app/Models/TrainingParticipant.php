<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrainingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_session_id',
        'employee_id',
        'attendance_status',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function history(): HasOne
    {
        return $this->hasOne(TrainingHistory::class);
    }
}
