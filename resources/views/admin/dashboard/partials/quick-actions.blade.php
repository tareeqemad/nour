@if(auth()->user()->isEmployee() || auth()->user()->isTechnician())
    @include('admin.dashboard.partials.quick-actions.employee-technician')
@elseif(auth()->user()->isCompanyOwner())
    @include('admin.dashboard.partials.quick-actions.company-owner')
@elseif(auth()->user()->isAdmin())
    @include('admin.dashboard.partials.quick-actions.admin')
@elseif(auth()->user()->isSuperAdmin())
    @include('admin.dashboard.partials.quick-actions.super-admin')
@endif




