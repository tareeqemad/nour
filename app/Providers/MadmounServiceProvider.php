<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * منصة "مضمون" — مركز الربط الوحيد.
 *
 * كل ما يخص مضمون مُسجّل من هنا، بمعزل عن نواة نور:
 *   - الراوتس:    routes/madmoun.php            (prefix: /madmoun ، أسماء: madmoun.*)
 *   - الفيوهات:   resources/views/madmoun       (namespace: madmoun::)
 *   - المايقريشن: database/migrations/madmoun    (جداول بادئتها madmoun_)
 *
 * مبرمج مضمون يشتغل داخل هذه المساحات فقط ولا يحتاج تعديل أي ملف في النواة.
 */
class MadmounServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // فيوهات مضمون: madmoun::dashboard ، madmoun::layouts.app ...
        $this->loadViewsFrom(resource_path('views/madmoun'), 'madmoun');

        // مايقريشن مضمون معزولة في مجلدها الخاص
        $this->loadMigrationsFrom(database_path('migrations/madmoun'));

        // راوتس مضمون: نفس طبقة web، بادئة /madmoun وأسماء madmoun.*
        Route::middleware('web')
            ->prefix('madmoun')
            ->name('madmoun.')
            ->group(base_path('routes/madmoun.php'));
    }
}
