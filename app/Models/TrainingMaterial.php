<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_module_id',
        'title',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
    ];

    public function trainingModule(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class);
    }

    /**
     * Ukuran file dalam format yang mudah dibaca (KB/MB) — dihitung dinamis
     * dari file_size (bytes), bukan disimpan sebagai string statis.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $kb = $this->file_size / 1024;

        return $kb < 1024
            ? round($kb, 1) . ' KB'
            : round($kb / 1024, 1) . ' MB';
    }
}
