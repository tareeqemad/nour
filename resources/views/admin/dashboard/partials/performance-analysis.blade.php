@if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
    @include('admin.dashboard.partials.performance-analysis.employee-technician')
@elseif(auth()->user()->isAdmin())
    @include('admin.dashboard.partials.performance-analysis.admin')
@elseif(auth()->user()->isCompanyOwner())
    @include('admin.dashboard.partials.performance-analysis.company-owner')
@elseif(auth()->user()->isSuperAdmin())
    @include('admin.dashboard.partials.performance-analysis.super-admin')
@endif




