<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $siteName = \App\Models\Setting::get('site_name', 'نور');
    @endphp
    <title>{{ $siteName }} - خريطة المشغلين</title>
    <meta name="description" content="خريطة تفاعلية لعرض جميع المشغلين في محافظات غزة. ابحث عن المشغلين في محافظتك واحصل على معلومات الاتصال الكاملة.">
    <meta name="keywords" content="{{ $siteName }}, خريطة المشغلين, مولدات كهرباء, غزة, محافظات غزة, مشغلين">
    <meta name="author" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $siteName }} - خريطة المشغلين">
    <meta property="og:description" content="خريطة تفاعلية لعرض جميع المشغلين في محافظات غزة">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url('/map') }}">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
    
    <!-- Tajawal Font from Admin Panel -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background: #f1f5f9;
            min-height: 100vh;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        /* Focus styles for accessibility */
        button:focus,
        select:focus,
        input:focus {
            outline: 3px solid rgba(25, 34, 143, 0.5);
            outline-offset: 2px;
        }
        
        /* Skip to content link for screen readers */
        .skip-link {
            position: absolute;
            top: -40px;
            right: 0;
            background: #19228f;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            z-index: 100;
            border-radius: 0 0 4px 4px;
        }
        
        .skip-link:focus {
            top: 0;
        }
        
        .header {
            background: #19228f;
            color: white;
            padding: 40px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            min-height: 120px;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('{{ asset("assets/front/images/generator-pattern.svg") }}');
            background-size: 200px 200px;
            background-repeat: repeat;
            opacity: 0.1;
            z-index: 0;
        }
        
        .header-map-background {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            opacity: 0.35;
            z-index: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .header-map-background svg {
            width: 100%;
            height: auto;
            max-width: 900px;
            max-height: 550px;
            min-width: 500px;
        }
        
        @media (max-width: 768px) {
            .header-map-background svg {
                max-width: 600px;
                min-width: 300px;
            }
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
        }
        
        .header-title-section {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header-map-icon {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-map-icon svg {
            width: 36px;
            height: 36px;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }
        
        .header h1 {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 2px 0;
            letter-spacing: -0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }
        
        .complaints-btn {
            background: rgba(255, 255, 255, 0.95);
            color: #19228f;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .complaints-btn:hover {
            background: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .complaints-btn:active {
            transform: translateY(0);
        }
        
        .complaints-btn svg {
            width: 18px;
            height: 18px;
            stroke-width: 2;
            flex-shrink: 0;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .main-content {
            display: flex;
            gap: 20px;
            flex-direction: row;
        }
        
        .sidebar {
            width: 350px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-height: 700px;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        .sidebar-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .sidebar-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .sidebar-header .count {
            font-size: 14px;
            color: #64748b;
        }
        
        .operators-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .operators-list li {
            padding: 12px;
            margin-bottom: 8px;
            background: #f8fafc;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .operators-list li:hover {
            background: #f1f5f9;
            border-color: #19228f;
            transform: translateX(-3px);
        }
        
        .operators-list li.active {
            background: #eff6ff;
            border-color: #19228f;
        }
        
        .governorate-section {
            margin-bottom: 20px;
        }
        
        .governorate-header {
            background: #19228f;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .governorate-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .governorate-operators {
            padding-right: 8px;
        }
        
        .operator-item-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .operator-item-details {
            font-size: 12px;
            color: #64748b;
        }
        
        .map-wrapper {
            flex: 1;
            min-width: 0;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .row > * {
            padding: 0 10px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: #19228f;
            color: white;
            padding: 20px 25px;
            border-bottom: none;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            color: white;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #475569;
            font-size: 14px;
        }
        
        .form-group select {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Tajawal', sans-serif;
            background: white;
            color: #1e293b;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #19228f;
            box-shadow: 0 0 0 3px rgba(25, 34, 143, 0.1);
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .search-btn {
            padding: 12px 24px;
            background: #19228f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .search-btn:hover {
            background: #141a6b;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(25, 34, 143, 0.3);
        }
        
        .search-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .welcome-message {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }
        
        .welcome-message.hidden {
            display: none;
        }
        
        .sidebar-search {
            margin-bottom: 15px;
        }
        
        .sidebar-search input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Tajawal', sans-serif;
            transition: all 0.3s;
        }
        
        .sidebar-search input:focus {
            outline: none;
            border-color: #19228f;
            box-shadow: 0 0 0 3px rgba(25, 34, 143, 0.1);
        }
        
        .no-results {
            text-align: center;
            padding: 30px 20px;
            color: #64748b;
            font-size: 14px;
        }
        
        .map-row {
            display: none !important;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .map-row.show {
            display: flex !important;
        }
        
        .map-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: 600px;
            position: relative;
            isolation: isolate;
        }
        
        #map {
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            min-width: 200px;
        }
        
        .loading.active {
            display: flex;
        }
        
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #19228f;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }
        
        .loading p {
            margin: 0;
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .no-operators {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-align: center;
            display: none;
        }
        
        .no-operators.active {
            display: block;
        }
        
        .no-operators h3 {
            color: #64748b;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .no-operators p {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .info-window {
            padding: 10px;
        }
        
        .info-window h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .info-window p {
            font-size: 14px;
            color: #64748b;
            margin: 4px 0;
        }
        
        .info-window .label {
            font-weight: 600;
            color: #475569;
        }
        
        .stats {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .stat-card {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }
        
        .stat-card-governorate {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .stat-card-count {
            font-size: 24px;
            font-weight: 700;
            color: #19228f;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stat-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
        }
        
        .stat-value {
            font-weight: 700;
            color: #19228f;
            font-size: 18px;
        }
        
        .info-window {
            min-width: 280px;
            max-width: 350px;
        }
        
        .info-window h3 {
            background: #19228f;
            color: white;
            padding: 12px 15px;
            margin: -10px -10px 12px -10px;
            border-radius: 8px 8px 0 0;
            font-size: 16px;
            font-weight: 700;
        }
        
        .info-window .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-window .info-row:last-child {
            border-bottom: none;
        }
        
        .info-window .info-label {
            font-weight: 600;
            color: #475569;
            font-size: 13px;
        }
        
        .info-window .info-value {
            color: #1e293b;
            font-size: 13px;
            text-align: left;
            flex: 1;
            margin-right: 10px;
        }
        
        .info-window .info-value a {
            color: #19228f;
            text-decoration: underline;
            font-weight: 500;
        }
        
        .info-window .info-value a:hover {
            color: #141a6b;
        }
        
        .info-window .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #19228f;
        }
        
        .map-container {
            height: 700px;
        }
        
        /* تخصيص النوافذ المنبثقة */
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .leaflet-popup-content {
            margin: 0;
        }
        
        .custom-popup .leaflet-popup-content-wrapper {
            padding: 0;
        }
        
        /* تخصيص العلامات */
        .custom-marker {
            background: transparent !important;
            border: none !important;
        }
        
        .map-controls {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1000;
            background: white;
            padding: 8px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 140px;
            pointer-events: auto;
        }
        
        .map-controls button {
            display: block;
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            transition: all 0.2s ease;
            text-align: right;
        }
        
        .map-controls button:hover {
            background: #f8fafc;
            border-color: #19228f;
            color: #19228f;
            transform: translateX(-2px);
        }
        
        .map-controls button.active {
            background: #19228f;
            border-color: #19228f;
            color: white;
            box-shadow: 0 2px 6px rgba(25, 34, 143, 0.3);
        }
        
        .map-controls button.active:hover {
            background: #19228f;
            transform: translateX(-2px);
        }
        
        @media (max-width: 1024px) {
            .map-row {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100% !important;
                max-height: 300px;
            }
            
            .map-wrapper {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 30px 15px;
                min-height: 100px;
            }
            
            .header-content {
                flex-wrap: wrap;
                gap: 12px;
            }
            
            .header-title-section {
                width: 100%;
                order: 1;
                gap: 12px;
            }
            
            .header-map-icon {
                width: 45px;
                height: 45px;
            }
            
            .header-map-icon svg {
                width: 24px;
                height: 24px;
            }
            
            .header h1 {
                font-size: 20px;
                white-space: normal;
            }
            
            .header p {
                font-size: 12px;
            }
            
            .complaints-btn {
                order: 2;
                padding: 9px 16px;
                font-size: 13px;
            }
            
            .complaints-btn svg {
                width: 16px;
                height: 16px;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .card-body {
                padding: 20px !important;
            }
            
            .search-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-btn {
                width: 100%;
            }
            
            .welcome-message {
                padding: 15px;
            }
            
            .welcome-message svg {
                width: 48px !important;
                height: 48px !important;
            }
            
            .welcome-message h3 {
                font-size: 16px !important;
            }
            
            .welcome-message p {
                font-size: 13px !important;
            }
            
            .map-container {
                height: 500px;
            }
            
            .info-window {
                min-width: 250px;
                max-width: 300px;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .map-controls {
                top: 10px;
                right: 10px;
                padding: 6px;
                min-width: 120px;
            }
            
            .map-controls button {
                padding: 8px 12px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 18px;
            }
            
            .header p {
                display: none;
            }
            
            .complaints-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">انتقل إلى المحتوى الرئيسي</a>
    <div class="header">
        <div class="header-map-background">
            <svg viewBox="0 0 800 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Gaza Strip Map Outline -->
                <path d="M 100 200 Q 150 150 200 180 T 300 200 T 400 220 T 500 210 T 600 230 T 700 250 L 720 300 L 700 350 L 600 340 L 500 330 L 400 320 L 300 310 L 200 300 L 100 280 Z" 
                      fill="rgba(255,255,255,0.15)" 
                      stroke="rgba(255,255,255,0.5)" 
                      stroke-width="4" 
                      stroke-linejoin="round"/>
                
                <!-- Governorate boundaries -->
                <path d="M 200 200 L 200 300" stroke="rgba(255,255,255,0.4)" stroke-width="3" stroke-dasharray="6,4"/>
                <path d="M 350 210 L 350 320" stroke="rgba(255,255,255,0.4)" stroke-width="3" stroke-dasharray="6,4"/>
                <path d="M 500 220 L 500 330" stroke="rgba(255,255,255,0.4)" stroke-width="3" stroke-dasharray="6,4"/>
                
                <!-- Location markers (cities/governorates) -->
                <circle cx="150" cy="240" r="10" fill="rgba(255,255,255,0.6)" stroke="rgba(255,255,255,0.9)" stroke-width="2.5"/>
                <circle cx="275" cy="250" r="10" fill="rgba(255,255,255,0.6)" stroke="rgba(255,255,255,0.9)" stroke-width="2.5"/>
                <circle cx="425" cy="260" r="10" fill="rgba(255,255,255,0.6)" stroke="rgba(255,255,255,0.9)" stroke-width="2.5"/>
                <circle cx="575" cy="280" r="10" fill="rgba(255,255,255,0.6)" stroke="rgba(255,255,255,0.9)" stroke-width="2.5"/>
                <circle cx="650" cy="290" r="10" fill="rgba(255,255,255,0.6)" stroke="rgba(255,255,255,0.9)" stroke-width="2.5"/>
                
                <!-- Connection lines between locations -->
                <path d="M 150 240 L 275 250 L 425 260 L 575 280 L 650 290" 
                      stroke="rgba(255,255,255,0.35)" 
                      stroke-width="3" 
                      fill="none" 
                      stroke-dasharray="10,5"/>
                
                <!-- Grid pattern for map detail -->
                <defs>
                    <pattern id="mapGrid" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                        <path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect x="100" y="200" width="620" height="150" fill="url(#mapGrid)" opacity="0.3"/>
                
                <!-- Compass rose -->
                <g transform="translate(680, 120)">
                    <circle r="25" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
                    <line x1="0" y1="-20" x2="0" y2="20" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <line x1="-20" y1="0" x2="20" y2="0" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <polygon points="0,-25 5,-15 -5,-15" fill="rgba(255,255,255,0.5)"/>
                </g>
            </svg>
        </div>
        <div class="header-content">
            <div class="header-title-section">
                <div class="header-map-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Map outline -->
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" fill="none"/>
                        <!-- Location pin -->
                        <circle cx="12" cy="10" r="3" stroke="currentColor" fill="rgba(255,255,255,0.3)"/>
                        <!-- Grid lines for map detail -->
                        <line x1="8" y1="6" x2="8" y2="14" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                        <line x1="16" y1="6" x2="16" y2="14" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                        <line x1="6" y1="8" x2="18" y2="8" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                        <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                        <!-- Small location markers -->
                        <circle cx="8" cy="8" r="1.5" fill="currentColor" opacity="0.6"/>
                        <circle cx="16" cy="12" r="1.5" fill="currentColor" opacity="0.6"/>
                    </svg>
                </div>
                <div>
                    <h1>{{ $siteName }} - خريطة المشغلين</h1>
                    <p>استكشف مواقع المشغلين على الخريطة</p>
                </div>
            </div>
            <a href="{{ route('complaints-suggestions.index') }}" class="complaints-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    <line x1="9" y1="10" x2="15" y2="10"></line>
                    <line x1="12" y1="7" x2="12" y2="13"></line>
                </svg>
                <span>المقترحات والشكاوى</span>
            </a>
        </div>
    </div>
    
    <div class="container" id="main-content">
        <div class="row">
            <div class="card" style="width: 100%;">
                <div class="card-header">
                    <h2 class="card-title">اختر المحافظة</h2>
                </div>
                <div class="card-body">
                    <div class="welcome-message" id="welcomeMessage">
                        <div style="text-align: center; padding: 20px; color: #64748b;">
                            <svg style="width: 64px; height: 64px; margin: 0 auto 15px; display: block; color: #19228f; opacity: 0.6;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <h3 style="color: #1e293b; margin-bottom: 10px; font-size: 18px;">مرحباً بك في خريطة المشغلين</h3>
                            <p style="font-size: 14px; line-height: 1.6;">اختر المحافظة من القائمة أدناه ثم اضغط على زر البحث للعثور على المشغلين في محافظتك</p>
                        </div>
                    </div>
                    
                    <div class="search-form">
                        <div class="form-group" style="flex: 1;">
                            <label for="governorate">المحافظة</label>
                            <select id="governorate" name="governorate">
                                <option value="">-- اختر المحافظة --</option>
                                @foreach($governorates as $governorate)
                                    <option value="{{ $governorate->value }}">{{ $governorate->label }}</option>
                                @endforeach
                                <option value="all">جميع المحافظات</option>
                            </select>
                        </div>
                        <button type="button" class="search-btn" id="searchBtn" aria-label="بحث عن المشغلين">بحث</button>
                        <button type="button" class="search-btn" id="clearBtn" style="display: none; background: #64748b;" aria-label="مسح البحث">مسح</button>
                    </div>
                    
                    <div class="stats" id="stats" style="display: none; margin-top: 20px;">
                        <!-- سيتم ملؤها ديناميكياً -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row map-row" id="mapRow">
            <div class="sidebar" id="sidebar" style="display: none; width: 350px; flex-shrink: 0;">
                <div class="sidebar-header">
                    <h3>قائمة المشغلين</h3>
                    <div class="count">عدد المشغلين: <span id="sidebarCount">0</span></div>
                </div>
                <div class="sidebar-search">
                    <input type="text" id="operatorSearch" placeholder="🔍 ابحث عن مشغل..." aria-label="بحث في قائمة المشغلين">
                </div>
                <div class="no-results" id="noResults" style="display: none;">
                    لا توجد نتائج للبحث
                </div>
                <ul class="operators-list" id="operatorsList">
                    <!-- سيتم ملؤها ديناميكياً -->
                </ul>
            </div>
            
            <div class="map-wrapper" style="flex: 1; min-width: 0;">
                <div class="map-container">
                    <div class="map-controls" role="group" aria-label="نوع الخريطة">
                        <button id="mapTypeStreet" class="active" aria-label="خريطة تفصيلية">خريطة تفصيلية</button>
                        <button id="mapTypeSatellite" aria-label="قمر صناعي">قمر صناعي</button>
                    </div>
                    
                    <div class="loading" id="loading">
                        <div class="loading-spinner"></div>
                        <p>جاري تحميل البيانات...</p>
                    </div>
                    
                    <div class="no-operators" id="noOperators">
                        <h3>لا توجد مشغلين</h3>
                        <p>لا توجد مشغلين في المحافظة المختارة</p>
                    </div>
                    
                    <div id="map" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leaflet JS -->
    <script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
    
    <script>
        // مسارات الأيقونات المحلية
        const markerIconsBase = '{{ asset("assets/leaflet/images/markers") }}';
        const markerShadowPath = '{{ asset("assets/leaflet/images/marker-shadow.png") }}';
        
        // إحداثيات قطاع غزة الافتراضية (لإظهار القطاع كاملاً)
        const defaultLat = 31.3547;
        const defaultLng = 34.3088;
        const defaultZoom = 10.5;
        
        // تهيئة الخريطة (لن يتم تهيئتها إلا بعد الاستعلام)
        let map = null;
        
        // طبقات الخريطة مع معالم أكثر
        const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        });
        
        // طبقة OpenStreetMap مع معالم محسّنة
        const detailedStreetLayer = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hot.openstreetmap.org/" target="_blank">HOT</a>',
            maxZoom: 19
        });
        
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; <a href="https://www.esri.com/">Esri</a>',
            maxZoom: 19
        });
        
        
        // متغيرات الخريطة
        let currentLayer = null;
        let markersGroup = null;
        
        // دالة لتهيئة الخريطة
        function initMap() {
            if (map) {
                // إذا كانت الخريطة مهيأة بالفعل، فقط أظهرها
                document.getElementById('mapRow').classList.add('show');
                return;
            }
            
            // إظهار صف الخريطة أولاً
            const mapRow = document.getElementById('mapRow');
            mapRow.classList.add('show');
            
            // انتظر قليلاً لضمان أن العنصر ظاهر قبل تهيئة الخريطة
            setTimeout(() => {
                map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);
                currentLayer = detailedStreetLayer;
                currentLayer.addTo(map);
                markersGroup = L.layerGroup().addTo(map);
                
                // إعادة حساب حجم الخريطة بعد إظهارها
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
                
                // إظهار أزرار التحكم
                mapTypeStreet.addEventListener('click', () => changeMapType('street'));
                mapTypeSatellite.addEventListener('click', () => changeMapType('satellite'));
            }, 50);
        }
        
        // تخزين المشغلين الحاليين
        let currentOperators = [];
        let currentMarkers = {};
        
        // عناصر DOM
        const governorateSelect = document.getElementById('governorate');
        const loadingDiv = document.getElementById('loading');
        const noOperatorsDiv = document.getElementById('noOperators');
        const sidebar = document.getElementById('sidebar');
        const operatorsList = document.getElementById('operatorsList');
        const sidebarCount = document.getElementById('sidebarCount');
        const statsDiv = document.getElementById('stats');
        const searchBtn = document.getElementById('searchBtn');
        const clearBtn = document.getElementById('clearBtn');
        const welcomeMessage = document.getElementById('welcomeMessage');
        const operatorSearch = document.getElementById('operatorSearch');
        const noResults = document.getElementById('noResults');
        
        // حفظ آخر محافظة في localStorage
        const lastGovernorate = localStorage.getItem('lastGovernorate');
        if (lastGovernorate) {
            governorateSelect.value = lastGovernorate;
        }
        
        // أزرار تغيير نوع الخريطة
        const mapTypeStreet = document.getElementById('mapTypeStreet');
        const mapTypeSatellite = document.getElementById('mapTypeSatellite');
        
        // معالجة تغيير نوع الخريطة
        function changeMapType(type) {
            if (!map) return;
            
            map.removeLayer(currentLayer);
            
            // إزالة active من جميع الأزرار
            mapTypeStreet.classList.remove('active');
            mapTypeSatellite.classList.remove('active');
            
            switch(type) {
                case 'street':
                    currentLayer = detailedStreetLayer; // استخدام الطبقة المحسّنة
                    mapTypeStreet.classList.add('active');
                    break;
                case 'satellite':
                    currentLayer = satelliteLayer;
                    mapTypeSatellite.classList.add('active');
                    break;
            }
            
            currentLayer.addTo(map);
        }
        
        // دالة لإظهار/إخفاء التحميل
        function showLoading(show) {
            if (show) {
                loadingDiv.classList.add('active');
                noOperatorsDiv.classList.remove('active');
                searchBtn.disabled = true;
                searchBtn.textContent = 'جاري البحث...';
            } else {
                loadingDiv.classList.remove('active');
                searchBtn.disabled = false;
                searchBtn.textContent = 'بحث';
            }
        }
        
        // دالة لإظهار رسالة عدم وجود مشغلين
        function showNoOperators(show) {
            if (show) {
                noOperatorsDiv.classList.add('active');
            } else {
                noOperatorsDiv.classList.remove('active');
            }
        }
        
        // دالة لتحميل المشغلين وعرضهم على الخريطة
        async function loadOperators(governorate) {
            if (!governorate || governorate === '') {
                // إخفاء الخريطة
                const mapRow = document.getElementById('mapRow');
                mapRow.classList.remove('show');
                mapRow.style.display = 'none';
                if (markersGroup) {
                    markersGroup.clearLayers();
                }
                if (map) {
                    map.remove();
                    map = null;
                    currentLayer = null;
                    markersGroup = null;
                }
                showNoOperators(false);
                statsDiv.style.display = 'none';
                sidebar.style.display = 'none';
                clearBtn.style.display = 'none';
                welcomeMessage.classList.remove('hidden');
                currentOperators = [];
                currentMarkers = {};
                localStorage.removeItem('lastGovernorate');
                return;
            }
            
            // حفظ المحافظة في localStorage
            localStorage.setItem('lastGovernorate', governorate);
            
            // إخفاء رسالة الترحيب
            welcomeMessage.classList.add('hidden');
            clearBtn.style.display = 'inline-block';
            
            // تهيئة الخريطة إذا لم تكن مهيأة
            initMap();
            
            showLoading(true);
            if (markersGroup) {
                markersGroup.clearLayers();
            }
            statsDiv.style.display = 'none';
            sidebar.style.display = 'none';
            currentOperators = [];
            currentMarkers = {};
            
            try {
                const response = await fetch(`{{ route('front.operators.map') }}?governorate=${governorate}`);
                const data = await response.json();
                
                showLoading(false);
                
                if (data.success && data.data.length > 0) {
                    showNoOperators(false);
                    
                    // حفظ البيانات
                    currentOperators = data.data;
                    currentMarkers = {};
                    
                    // تحديث الإحصائيات
                    updateStats(data.data);
                    
                    // إظهار القائمة الجانبية
                    sidebar.style.display = 'block';
                    updateSidebar(data.data);
                    
                    // إضافة علامات للمشغلين
                    const bounds = [];
                    
                    // ألوان مختلفة حسب المحافظة (ألوان Leaflet الأصلية)
                    const markerColors = {
                        'غزة': 'blue',
                        'الوسطى': 'green',
                        'خانيونس': 'orange',
                        'رفح': 'red'
                    };
                    
                    // دالة لإنشاء أيقونة Leaflet بألوان مختلفة
                    function createColoredIcon(color) {
                        return L.icon({
                            iconUrl: `${markerIconsBase}/marker-icon-2x-${color}.png`,
                            shadowUrl: markerShadowPath,
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        });
                    }
                    
                    data.data.forEach((operator, index) => {
                        const color = markerColors[operator.governorate] || 'blue';
                        const icon = createColoredIcon(color);
                        
                        const marker = L.marker([operator.latitude, operator.longitude], {
                            icon: icon
                        });
                        
                        if (markersGroup) {
                            marker.addTo(markersGroup);
                        }
                        
                        // حفظ المرجع
                        currentMarkers[operator.id] = marker;
                        
                        // إنشاء محتوى النافذة المعلوماتية - البيانات الأساسية فقط
                        let popupContent = `
                            <div class="info-window">
                                <h3>${operator.name}</h3>
                                ${operator.governorate ? `
                                    <div class="info-row">
                                        <span class="info-label">المحافظة:</span>
                                        <span class="info-value">${operator.governorate}</span>
                                    </div>
                                ` : ''}
                                ${operator.city ? `
                                    <div class="info-row">
                                        <span class="info-label">المدينة:</span>
                                        <span class="info-value">${operator.city}</span>
                                    </div>
                                ` : ''}
                                ${operator.phone ? `
                                    <div class="info-row">
                                        <span class="info-label">الهاتف:</span>
                                        <span class="info-value"><a href="tel:${operator.phone}">${operator.phone}</a></span>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                        
                        marker.bindPopup(popupContent, {
                            maxWidth: 350,
                            className: 'custom-popup'
                        });
                        
                        // إضافة حدث للنقر على العلامة
                        marker.on('click', function() {
                            highlightOperatorInSidebar(operator.id);
                        });
                        
                        bounds.push([operator.latitude, operator.longitude]);
                    });
                    
                    // تكبير الخريطة لتشمل جميع العلامات
                    if (map && bounds.length > 0) {
                        if (bounds.length === 1) {
                            map.setView(bounds[0], 15);
                        } else {
                            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                        }
                    }
                } else {
                    showNoOperators(true);
                    sidebar.style.display = 'none';
                    statsDiv.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading operators:', error);
                showLoading(false);
                showNoOperators(true);
                
                // إظهار رسالة خطأ أفضل
                const errorMsg = document.createElement('div');
                errorMsg.style.cssText = 'position: absolute; top: 20px; right: 20px; background: #fee2e2; color: #991b1b; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 2000; max-width: 300px;';
                errorMsg.innerHTML = '<strong>⚠️ خطأ!</strong><br>حدث خطأ أثناء تحميل البيانات. يرجى المحاولة مرة أخرى.';
                document.body.appendChild(errorMsg);
                
                setTimeout(() => {
                    errorMsg.remove();
                }, 5000);
            }
        }
        
        // دالة لتحديث الإحصائيات
        function updateStats(operators) {
            const stats = {};
            
            operators.forEach(op => {
                const gov = op.governorate || 'غير محدد';
                stats[gov] = (stats[gov] || 0) + 1;
            });
            
            let statsHTML = '';
            Object.keys(stats).forEach(gov => {
                statsHTML += `
                    <div class="stat-card">
                        <div class="stat-card-governorate">${gov}</div>
                        <div class="stat-card-count">${stats[gov]}</div>
                    </div>
                `;
            });
            
            statsDiv.innerHTML = statsHTML;
            statsDiv.style.display = 'grid';
        }
        
        // دالة لتحديث القائمة الجانبية
        function updateSidebar(operators) {
            sidebarCount.textContent = operators.length;
            operatorsList.innerHTML = '';
            allOperators = operators; // حفظ جميع المشغلين للبحث
            operatorSearch.value = ''; // مسح البحث
            noResults.style.display = 'none';
            
            // التحقق إذا كان هناك أكثر من محافظة (جميع المحافظات)
            const uniqueGovernorates = [...new Set(operators.map(op => op.governorate).filter(Boolean))];
            const isMultipleGovernorates = uniqueGovernorates.length > 1;
            
            if (isMultipleGovernorates) {
                // تجميع المشغلين حسب المحافظة
                const groupedByGovernorate = {};
                operators.forEach(operator => {
                    const gov = operator.governorate || 'غير محدد';
                    if (!groupedByGovernorate[gov]) {
                        groupedByGovernorate[gov] = [];
                    }
                    groupedByGovernorate[gov].push(operator);
                });
                
                // ترتيب المحافظات
                const governorateOrder = ['غزة', 'الوسطى', 'خانيونس', 'رفح'];
                const sortedGovernorates = Object.keys(groupedByGovernorate).sort((a, b) => {
                    const indexA = governorateOrder.indexOf(a);
                    const indexB = governorateOrder.indexOf(b);
                    if (indexA === -1 && indexB === -1) return a.localeCompare(b);
                    if (indexA === -1) return 1;
                    if (indexB === -1) return -1;
                    return indexA - indexB;
                });
                
                // إنشاء أقسام لكل محافظة
                sortedGovernorates.forEach(governorate => {
                    const section = document.createElement('div');
                    section.className = 'governorate-section';
                    
                    const header = document.createElement('div');
                    header.className = 'governorate-header';
                    header.innerHTML = `
                        <span>${governorate}</span>
                        <span class="governorate-count">${groupedByGovernorate[governorate].length}</span>
                    `;
                    section.appendChild(header);
                    
                    const operatorsContainer = document.createElement('div');
                    operatorsContainer.className = 'governorate-operators';
                    
                    groupedByGovernorate[governorate].forEach(operator => {
                        const li = document.createElement('li');
                        li.dataset.operatorId = operator.id;
                        li.innerHTML = `
                            <div class="operator-item-name">${operator.name}</div>
                            <div class="operator-item-details">
                                ${operator.city ? operator.city : ''}
                                ${operator.phone ? '<br>📞 ' + operator.phone : ''}
                            </div>
                        `;
                        
                        li.addEventListener('click', function() {
                            const marker = currentMarkers[operator.id];
                            if (marker) {
                                map.setView([operator.latitude, operator.longitude], 15);
                                marker.openPopup();
                                highlightOperatorInSidebar(operator.id);
                            }
                        });
                        
                        operatorsContainer.appendChild(li);
                    });
                    
                    section.appendChild(operatorsContainer);
                    operatorsList.appendChild(section);
                });
            } else {
                // محافظة واحدة - عرض عادي بدون أقسام
                operators.forEach(operator => {
                    const li = document.createElement('li');
                    li.dataset.operatorId = operator.id;
                    li.innerHTML = `
                        <div class="operator-item-name">${operator.name}</div>
                        <div class="operator-item-details">
                            ${operator.city ? operator.city : ''}
                            ${operator.phone ? '<br>📞 ' + operator.phone : ''}
                        </div>
                    `;
                    
                    li.addEventListener('click', function() {
                        const marker = currentMarkers[operator.id];
                        if (marker) {
                            map.setView([operator.latitude, operator.longitude], 15);
                            marker.openPopup();
                            highlightOperatorInSidebar(operator.id);
                        }
                    });
                    
                    operatorsList.appendChild(li);
                });
            }
        }
        
        // دالة لتحديد المشغل في القائمة الجانبية
        function highlightOperatorInSidebar(operatorId) {
            const items = operatorsList.querySelectorAll('li');
            items.forEach(item => {
                if (item.dataset.operatorId == operatorId) {
                    item.classList.add('active');
                    // Scroll to the parent section if exists
                    const section = item.closest('.governorate-section');
                    if (section) {
                        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                } else {
                    item.classList.remove('active');
                }
            });
        }
        
        // زر البحث
        searchBtn.addEventListener('click', function() {
            const governorate = governorateSelect.value;
            if (!governorate) {
                alert('يرجى اختيار محافظة أولاً');
                return;
            }
            loadOperators(governorate);
        });
        
        // زر مسح البحث
        clearBtn.addEventListener('click', function() {
            governorateSelect.value = '';
            loadOperators('');
        });
        
        // البحث في قائمة المشغلين
        let allOperators = [];
        operatorSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const listItems = operatorsList.querySelectorAll('li');
            let visibleCount = 0;
            
            if (searchTerm === '') {
                listItems.forEach(item => {
                    item.style.display = '';
                    visibleCount++;
                });
                noResults.style.display = 'none';
            } else {
                listItems.forEach(item => {
                    const operatorName = item.querySelector('.operator-item-name')?.textContent.toLowerCase() || '';
                    const operatorDetails = item.querySelector('.operator-item-details')?.textContent.toLowerCase() || '';
                    const matches = operatorName.includes(searchTerm) || operatorDetails.includes(searchTerm);
                    
                    if (matches) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
        
        // Enter key للبحث
        governorateSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    </script>
</body>
</html>

