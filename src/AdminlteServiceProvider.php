<?php

namespace Blocs;

use Illuminate\Support\ServiceProvider;

class AdminlteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerBlocsCommand();
    }

    public function boot()
    {
    }

    public function registerBlocsCommand()
    {
        $this->app->singleton('command.blocs.adminlte', function ($app) {
            return new \App\Console\Commands\Blocs('blocs:adminlte', 'Deploy blocs/adminlte package', __FILE__);
        });

        $this->commands('command.blocs.adminlte');
    }
}
