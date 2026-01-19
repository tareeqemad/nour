<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TracksUser
{
    /**
     * Boot the trait
     */
    public static function bootTracksUser(): void
    {
        // عند الإنشاء
        static::creating(function ($model) {
            if (Auth::check() && $model->getTable() !== 'users') {
                if ($model->hasColumn('created_by')) {
                    $model->created_by = Auth::id();
                }
                if ($model->hasColumn('last_updated_by')) {
                    $model->last_updated_by = Auth::id();
                }
            }
        });

        // عند التحديث
        static::updating(function ($model) {
            if (Auth::check() && $model->getTable() !== 'users') {
                if ($model->hasColumn('updated_by')) {
                    $model->updated_by = Auth::id();
                }
                if ($model->hasColumn('last_updated_by')) {
                    $model->last_updated_by = Auth::id();
                }
            }
        });
    }

    /**
     * التحقق من وجود عمود في الجدول
     */
    private function hasColumn(string $column): bool
    {
        try {
            $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
            return in_array($column, $columns);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ السجل
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * العلاقة مع المستخدم الذي حدث السجل
     */
    public function updater()
    {
        $column = $this->hasColumn('updated_by') ? 'updated_by' : 'last_updated_by';
        return $this->belongsTo(\App\Models\User::class, $column);
    }
}

