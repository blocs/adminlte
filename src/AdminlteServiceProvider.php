<?php

namespace Blocs;

use App\Console\Commands\BlocsAdminlte;
use Illuminate\Support\ServiceProvider;

class AdminlteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerBlocsAdminlteCommand();
    }

    public function boot()
    {
    }

    public function registerBlocsAdminlteCommand()
    {
        $this->app->singleton('command.blocs.adminlte', function ($app) {
            return new BlocsAdminlte();
        });

        $this->commands('command.blocs.adminlte');
    }
}
