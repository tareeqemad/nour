<?php

namespace App\Traits;

/**
 * Trait للتحقق من صلاحيات السوبر أدمن
 * 
 * يمكن استخدامه في أي Controller يحتاج التحقق من صلاحيات السوبر أدمن
 */
trait ChecksSuperAdmin
{
    /**
     * التحقق من صلاحيات السوبر أدمن
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function checkSuperAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }
    }
}
