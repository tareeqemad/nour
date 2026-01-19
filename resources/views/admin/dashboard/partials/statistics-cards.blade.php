<!-- Main Statistics Cards -->
<div class="row g-3 mb-4">
    @if(auth()->user()->isTechnician())
        @include('admin.dashboard.partials.statistics-cards.technician')
    @elseif(auth()->user()->isCivilDefense())
        @include('admin.dashboard.partials.statistics-cards.civil-defense')
    @elseif(auth()->user()->isEmployee())
        @include('admin.dashboard.partials.statistics-cards.employee-technician')
    @elseif(auth()->user()->isCompanyOwner())
        @include('admin.dashboard.partials.statistics-cards.company-owner')
    @elseif(auth()->user()->isAdmin())
        @include('admin.dashboard.partials.statistics-cards.admin')
    @elseif(auth()->user()->isSuperAdmin())
        @include('admin.dashboard.partials.statistics-cards.super-admin')
    @endif
</div>




