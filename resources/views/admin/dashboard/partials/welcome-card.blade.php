<div class="d-welcome-bar">
    <div class="d-welcome-right">
        <span class="d-welcome-name">مرحباً، {{ auth()->user()->name }}</span>
        @php $operator = auth()->user()->ownedOperators->first(); @endphp
        @if($operator)
        <span class="d-welcome-sep">&middot;</span>
        <span class="d-welcome-operator">{{ $operator->name }}</span>
        @endif
    </div>
    <div class="d-welcome-left">
        <span class="d-welcome-date"><i class="bi bi-calendar3 me-1"></i>{{ now('Asia/Gaza')->locale('ar')->translatedFormat('l، d F Y') }}</span>
        <span class="d-welcome-time">{{ now('Asia/Gaza')->format('H:i') }}</span>
    </div>
</div>
