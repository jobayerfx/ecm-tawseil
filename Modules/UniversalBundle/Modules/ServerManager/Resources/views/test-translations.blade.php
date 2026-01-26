<!DOCTYPE html>
<html>
<head>
    <title>Translation Test</title>
</head>
<body>
    <h1>Server Manager Translation Test</h1>

    <p><strong>Module Name:</strong> {{ __('servermanager::modules.servermanager') }}</p>
    <p><strong>Menu Item:</strong> {{ __('servermanager::app.menu.serverManager') }}</p>
    <p><strong>Dashboard:</strong> {{ __('servermanager::app.menu.dashboard') }}</p>
    <p><strong>Permission:</strong> {{ __('servermanager::permissions.view_hosting') }}</p>

    <h2>Raw Translation Keys:</h2>
    <p>servermanager::modules.servermanager</p>
    <p>servermanager::app.menu.serverManager</p>
    <p>servermanager::app.menu.dashboard</p>
    <p>servermanager::permissions.view_hosting</p>
</body>
</html>
