<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_module_id',
        'trainer_name',
        'session_date',
        'start_time',
        'end_time',
        'actual_duration_hours',
        'location',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function trainingModule(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TrainingParticipant::class);
    }
}
