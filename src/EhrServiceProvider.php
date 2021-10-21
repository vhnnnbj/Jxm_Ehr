<?php

namespace Jxm\Ehr;

use Illuminate\Support\ServiceProvider;

class EhrServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/ehr.php' => config_path('ehr.php'),
        ], 'ehr_config');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
