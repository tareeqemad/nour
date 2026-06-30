@extends('madmoun::layouts.app')

@section('title', 'منصة مضمون')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">منصة مضمون</div>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="bi bi-shield-check" style="font-size:3rem;color:var(--primary-color)"></i>
                        <h4 class="mt-3">الأساس جاهز ✅</h4>
                        <p class="text-muted mb-0">
                            هذه صفحة بداية منصة مضمون. مساحة العمل معزولة وجاهزة للتطوير:
                            راوتس <code>routes/madmoun.php</code> ·
                            كنترولرات <code>App\Http\Controllers\Madmoun</code> ·
                            موديلات <code>App\Models\Madmoun</code> ·
                            فيوهات <code>madmoun::</code> ·
                            مايقريشن <code>database/migrations/madmoun</code>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
