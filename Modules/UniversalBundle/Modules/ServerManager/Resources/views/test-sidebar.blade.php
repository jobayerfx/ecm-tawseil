<!DOCTYPE html>
<html>
<head>
    <title>Sidebar Test</title>
</head>
<body>
    <h1>Server Manager Sidebar Test</h1>

    <h2>Debug Information:</h2>
    <p><strong>User Authenticated:</strong> {{ auth()->check() ? 'YES' : 'NO' }}</p>
    @if(auth()->check())
        <p><strong>User ID:</strong> {{ auth()->id() }}</p>
        <p><strong>User Roles:</strong> {{ implode(', ', user_roles()) }}</p>
        <p><strong>User Modules:</strong> {{ implode(', ', user_modules()) }}</p>
        <p><strong>Module Name Constant:</strong> {{ \Modules\ServerManager\Entities\ServerSetting::MODULE_NAME }}</p>
        <p><strong>Module in User Modules:</strong> {{ in_array(\Modules\ServerManager\Entities\ServerSetting::MODULE_NAME, user_modules()) ? 'YES' : 'NO' }}</p>

        @php
            $hostingViewPermission = user()->permission('view_hosting');
            $hostingAddPermission = user()->permission('add_hosting');
            $hostingEditPermission = user()->permission('edit_hosting');
            $hostingDeletePermission = user()->permission('delete_hosting');
            $domainViewPermission = user()->permission('view_domain');
            $domainAddPermission = user()->permission('add_domain');
            $domainEditPermission = user()->permission('edit_domain');
            $domainDeletePermission = user()->permission('delete_domain');
            $statisticsPermission = user()->permission('view_server_statistics');
            $activitiesPermission = user()->permission('view_server_activities');
        @endphp

        <p><strong>Hosting View Permission:</strong> {{ $hostingViewPermission }}</p>
        <p><strong>Domain View Permission:</strong> {{ $domainViewPermission }}</p>
        <p><strong>Statistics Permission:</strong> {{ $statisticsPermission }}</p>
        <p><strong>Activities Permission:</strong> {{ $activitiesPermission }}</p>

        <p><strong>Sidebar Should Show:</strong>
            {{ (!in_array('client', user_roles()) && in_array(\Modules\ServerManager\Entities\ServerSetting::MODULE_NAME, user_modules()) && ($hostingViewPermission != 'none' || $hostingAddPermission != 'none' || $hostingEditPermission != 'none' || $hostingDeletePermission != 'none' || $domainViewPermission != 'none' || $domainAddPermission != 'none' || $domainEditPermission != 'none' || $domainDeletePermission != 'none' || $statisticsPermission != 'none' || $activitiesPermission != 'none')) ? 'YES' : 'NO' }}
        </p>
    @else
        <p>No authenticated user</p>
    @endif

    <h2>Sidebar Content:</h2>
    <div style="border: 1px solid #ccc; padding: 10px;">
        @include('servermanager::sections.sidebar')
    </div>
</body>
</html>
