<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OperatorAttachment extends Model
{
    protected $fillable = [
        'operator_id',
        'uploaded_by',
        'name',
        'file_path',
        'original_filename',
        'mime_type',
        'size',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getSizeForHumansAttribute(): string
    {
        if (! $this->size) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, $unitIndex === 0 ? 0 : 1) . ' ' . $units[$unitIndex];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $attachment) {
            if ($attachment->file_path) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        });
    }
}
