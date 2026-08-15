<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'is_mandatory',
        'standard_duration_hours',
        'validity_months',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(TrainingMaterial::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TrainingHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }
}
