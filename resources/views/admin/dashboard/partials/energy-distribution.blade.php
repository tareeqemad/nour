@if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
    @include('admin.dashboard.partials.energy-distribution.employee-technician')
@elseif(auth()->user()->isAdmin())
    @include('admin.dashboard.partials.energy-distribution.admin')
@elseif(auth()->user()->isCompanyOwner())
    @include('admin.dashboard.partials.energy-distribution.company-owner')
@elseif(auth()->user()->isSuperAdmin())
    @include('admin.dashboard.partials.energy-distribution.super-admin')
@endif




