{{--
    تخطيط منصة مضمون.

    يرث تخطيط نور بالكامل (نفس التصميم/الهيدر/الأصول) ويستبدل السايدبار فقط
    عبر @section('sidebar'). صفحات مضمون تمتد من هذا الملف:

        @extends('madmoun::layouts.app')
        @section('title', 'عنوان الصفحة')
        @section('content') ... @endsection

    لا حاجة لإعادة كتابة أي HTML للهيكل — كله موروث من layouts.admin.
--}}
@extends('layouts.admin')

@section('sidebar')
    @include('madmoun::partials.sidebar')
@endsection
