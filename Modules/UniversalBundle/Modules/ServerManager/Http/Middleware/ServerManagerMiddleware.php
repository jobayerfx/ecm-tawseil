<?php

namespace Modules\ServerManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\ServerManager\Entities\ServerSetting;

class ServerManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user has access to the ServerManager module
        if (!in_array(ServerSetting::MODULE_NAME, user()->modules)) {
            abort_403(__('messages.moduleNotActive'));
        }

        return $next($request);
    }
}
