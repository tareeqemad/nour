@if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
    @include('admin.dashboard.partials.operations-statistics.employee-technician')
@elseif(auth()->user()->isEnergyAuthority())
    @include('admin.dashboard.partials.operations-statistics.energy-authority')
@elseif(auth()->user()->isAdmin())
    @include('admin.dashboard.partials.operations-statistics.admin')
@elseif(auth()->user()->isCompanyOwner())
    @include('admin.dashboard.partials.operations-statistics.company-owner')
@elseif(auth()->user()->isSuperAdmin())
    @include('admin.dashboard.partials.operations-statistics.super-admin')
@endif




