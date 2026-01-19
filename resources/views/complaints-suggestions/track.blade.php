@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'متابعة الطلب - ' . $siteName)

@push('styles')
<style>
    .complaints-track-page {
        padding: 3rem 0 5rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
        min-height: calc(100vh - 70px);
    }

    .complaints-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .complaints-header {
        text-align: center;
        margin-bottom: 3rem;
        padding: 0;
        background: transparent;
        border: none;
        box-shadow: none;
        position: relative;
    }

    .complaints-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        display: inline-block;
        padding-bottom: 1.5rem;
    }
    
    .complaints-header h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        background: linear-gradient(90deg, transparent 0%, #3b82f6 20%, #10b981 50%, #3b82f6 80%, transparent 100%);
        border-radius: 2px;
    }
    
    .complaints-header p {
        font-size: 1.1rem;
        color: #64748b;
        font-weight: 500;
        position: relative;
        padding-bottom: 2rem;
        margin-top: 1rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .complaints-header p::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        max-width: 600px;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(59, 130, 246, 0.2) 20%, rgba(16, 185, 129, 0.3) 50%, rgba(59, 130, 246, 0.2) 80%, transparent 100%);
    }


    .search-form {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #f1f5f9;
    }

    .form-group {
        display: flex;
        gap: 1rem;
    }

    .form-input {
        flex: 1;
        padding: 0.875rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: 'Tajawal', sans-serif;
    }

    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .search-btn {
        padding: 0.875rem 2rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    }

    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .request-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 2px solid #f1f5f9;
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .request-card:hover {
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.2);
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .request-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-in_progress {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #2563eb;
    }

    .status-resolved {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .info-row {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .info-item {
        flex: 1;
        min-width: 200px;
    }

    .info-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .info-value {
        font-size: 1rem;
        color: #1e293b;
        font-weight: 600;
    }

    .tracking-code {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 2rem;
        border: 2px solid rgba(59, 130, 246, 0.1);
    }

    .tracking-code-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .tracking-code-value {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: 2px;
    }

    .message-box {
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .message-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }

    .message-content {
        font-size: 0.95rem;
        color: #1e293b;
        line-height: 1.7;
    }

    .response-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-right: 4px solid #3b82f6;
        padding: 1.5rem;
        border-radius: 12px;
        margin-top: 1.5rem;
    }

    .response-label {
        font-size: 1rem;
        font-weight: 700;
        color: #2563eb;
        margin-bottom: 0.75rem;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 1.5rem 0 1rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .section-title:first-of-type {
        margin-top: 1rem;
    }

    .response-content {
        font-size: 0.95rem;
        color: #1e293b;
        line-height: 1.7;
    }

    .alert {
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-right: 4px solid #10b981;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-right: 4px solid #ef4444;
    }

    .no-result {
        text-align: center;
        padding: 4rem 2rem;
        color: #64748b;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    @media (max-width: 768px) {
        .complaints-track-page {
            padding: 2rem 0 4rem;
        }

        .complaints-header {
            margin-bottom: 2rem;
        }
        
        .complaints-header h1 {
            font-size: 2rem;
        }
        
        .complaints-header p {
            font-size: 1rem;
        }

        .form-group {
            flex-direction: column;
        }

        .request-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .request-card {
            padding: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="complaints-track-page">
    <div class="complaints-container">
        <div class="complaints-header">
            <h1>متابعة الطلب</h1>
            <p>تابع حالة شكواك أو مقترحك</p>
        </div>

        <div>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error" id="errorAlert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="search-form">
                <form method="POST" action="{{ route('complaints-suggestions.search') }}">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="code" id="trackingCodeInput" class="form-input" placeholder="أدخل رمز التتبع" value="{{ $code ?? '' }}" required>
                        <button type="submit" class="search-btn">بحث</button>
                    </div>
                </form>
            </div>

            @if($complaintSuggestion)
                <div class="request-card" id="requestCard">
                    <div class="tracking-code">
                        <div class="tracking-code-label">رمز التتبع</div>
                        <div class="tracking-code-value">{{ $complaintSuggestion->tracking_code }}</div>
                    </div>

                    <div class="request-header">
                        <div class="request-title">بيانات الطلب</div>
                        <div class="status-badge status-{{ $complaintSuggestion->status }}">
                            {{ $complaintSuggestion->status_label }}
                        </div>
                    </div>

                    <!-- معلومات مقدم الطلب -->
                    <div class="section-title">معلومات مقدم الطلب</div>
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ $complaintSuggestion->name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">رقم الهاتف</div>
                            <div class="info-value">{{ $complaintSuggestion->phone }}</div>
                        </div>
                    </div>
                    @if($complaintSuggestion->email)
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">البريد الإلكتروني</div>
                            <div class="info-value">{{ $complaintSuggestion->email }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- معلومات الطلب -->
                    <div class="section-title">معلومات الطلب</div>
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">النوع</div>
                            <div class="info-value">{{ $complaintSuggestion->type_label }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">المحافظة</div>
                            <div class="info-value">{{ $complaintSuggestion->getGovernorateLabel() ?? 'غير محدد' }}</div>
                        </div>
                    </div>
                    @if($complaintSuggestion->generator)
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">المولد</div>
                            <div class="info-value">{{ $complaintSuggestion->generator->name }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">تاريخ الإرسال</div>
                            <div class="info-value">{{ $complaintSuggestion->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>

                    <!-- الرسالة -->
                    <div class="message-box">
                        <div class="message-label">الرسالة</div>
                        <div class="message-content">{{ $complaintSuggestion->message }}</div>
                    </div>

                    <!-- الصورة المرفقة -->
                    @if($complaintSuggestion->image)
                    <div class="message-box">
                        <div class="message-label">الصورة المرفقة</div>
                        <div style="margin-top: 10px;">
                            <img src="{{ asset('storage/' . $complaintSuggestion->image) }}" alt="صورة مرفقة" style="max-width: 100%; border-radius: 8px; border: 2px solid #e2e8f0;">
                        </div>
                    </div>
                    @endif

                    <!-- رد الإدارة -->
                    @if($complaintSuggestion->response)
                        <div class="response-box">
                            <div class="response-label">رد الإدارة</div>
                            <div class="response-content">{{ $complaintSuggestion->response }}</div>
                            @if($complaintSuggestion->responded_at)
                                <div style="margin-top: 10px; font-size: 0.85rem; color: #64748b;">
                                    بتاريخ: {{ $complaintSuggestion->responded_at->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="message-box" style="background: #fef3c7; border-right: 4px solid #f59e0b;">
                            <div style="font-size: 0.9rem; color: #92400e;">
                                ⏳ الطلب قيد المراجعة. سيتم الرد عليك قريباً.
                            </div>
                        </div>
                    @endif
                </div>
            @elseif($code)
                <div class="no-result" id="noResult">
                    <p>لم يتم العثور على طلب بهذا الرمز. يرجى التحقق من الرمز والمحاولة مرة أخرى.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.querySelector('.search-form form');
        const requestCard = document.getElementById('requestCard');
        const noResult = document.getElementById('noResult');
        const codeInput = document.getElementById('trackingCodeInput');
        const errorAlert = document.getElementById('errorAlert');

        // حذف النتائج السابقة عند بدء بحث جديد
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const code = codeInput ? codeInput.value.trim() : '';
                
                if (!code) {
                    e.preventDefault();
                    return;
                }

                // إخفاء النتائج السابقة قبل إرسال النموذج
                if (requestCard) {
                    requestCard.remove();
                }
                if (noResult) {
                    noResult.remove();
                }
                if (errorAlert) {
                    errorAlert.remove();
                }
            });
        }

        // عند تحميل الصفحة بدون نتائج، تأكد من عدم وجود نتائج سابقة
        @if($code && !$complaintSuggestion)
            // إذا كان هناك code ولكن لا توجد نتائج، احذف requestCard إذا كان موجوداً
            if (requestCard) {
                requestCard.remove();
            }
        @endif
    });
</script>
@endsection
