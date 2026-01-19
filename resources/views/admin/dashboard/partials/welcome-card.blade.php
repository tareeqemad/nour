<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="dashboard-welcome-card">
            <div class="dashboard-welcome-content">
                <div class="dashboard-welcome-text">
                    <h2 class="dashboard-welcome-title">
                        <i class="bi bi-hand-thumbs-up me-2"></i>
                        مرحباً بك {{ auth()->user()->name }} 👋
                    </h2>
                    <p class="dashboard-welcome-subtitle">
                        {{ now('Asia/Gaza')->locale('ar')->translatedFormat('l، d F Y') }}
                    </p>
                </div>
                <div class="dashboard-welcome-time">
                    <div class="dashboard-time-value">{{ now('Asia/Gaza')->format('H:i') }}</div>
                    <div class="dashboard-time-label">{{ now('Asia/Gaza')->locale('ar')->translatedFormat('A') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>




