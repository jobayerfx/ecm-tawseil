<?php

namespace Modules\ServerManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ServerManager\Console\ActivateModuleCommand;
use Modules\ServerManager\Console\CheckHostingExpiryCommand;
use Modules\ServerManager\Console\CheckDomainExpiryCommand;
use Modules\ServerManager\Entities\ServerHosting;
use Modules\ServerManager\Listeners\CompanyCreatedListener;
use App\Events\NewCompanyCreatedEvent;
use Illuminate\Support\Facades\Event;
use Modules\ServerManager\Entities\ServerDomain;
use Modules\ServerManager\Observers\ServerHostingObserver;
use Modules\ServerManager\Observers\ServerDomainObserver;

class ServerManagerServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->commands([
            ActivateModuleCommand::class,
            CheckHostingExpiryCommand::class,
            CheckDomainExpiryCommand::class,
        ]);

        // Register observers
        ServerHosting::observe(ServerHostingObserver::class);
        ServerDomain::observe(ServerDomainObserver::class);

        // Register event listeners
        Event::listen(NewCompanyCreatedEvent::class, CompanyCreatedListener::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('servermanager.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'servermanager'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/servermanager');

        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom([$sourcePath], 'servermanager');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/servermanager');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'servermanager');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'servermanager');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
