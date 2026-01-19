<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstantMaster extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'constant_number',
        'constant_name',
        'description',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(ConstantDetail::class)->where('is_active', true)->orderBy('order');
    }

    public function allDetails(): HasMany
    {
        return $this->hasMany(ConstantDetail::class)->orderBy('order');
    }

    public function activeDetails(): HasMany
    {
        return $this->hasMany(ConstantDetail::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * الحصول على ثابت من رقمه
     */
    public static function findByNumber(int $number): ?self
    {
        return static::where('constant_number', $number)->where('is_active', true)->first();
    }

    /**
     * الحصول على ثابت من اسمه
     */
    public static function findByName(string $name): ?self
    {
        return static::where('constant_name', $name)->where('is_active', true)->first();
    }
}
