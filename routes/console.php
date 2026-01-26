<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// جدولة التحقق من المولدات التي تحتاج صيانة يومياً
Schedule::command('maintenance:check')
    ->daily()
    ->at('08:00')
    ->description('التحقق من المولدات التي تحتاج صيانة وإرسال الإشعارات');

// جدولة تذكير مسبق للمولدات القريبة من الصيانة (أسبوعياً)
Schedule::command('maintenance:check --upcoming')
    ->weekly()
    ->mondays()
    ->at('08:00')
    ->description('تذكير مسبق للمولدات القريبة من الصيانة');
