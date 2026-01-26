<?php

namespace Modules\ServerManager\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Modules\ServerManager\Events\HostingCreated' => [
            'Modules\ServerManager\Listeners\HostingCreatedListener',
        ],
        'Modules\ServerManager\Events\HostingUpdated' => [
            // Add listeners when needed
        ],
        'Modules\ServerManager\Events\DomainCreated' => [
            'Modules\ServerManager\Listeners\DomainCreatedListener',
        ],
        'Modules\ServerManager\Events\DomainUpdated' => [
            // Add listeners when needed
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
