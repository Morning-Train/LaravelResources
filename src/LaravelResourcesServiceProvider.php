<?php

namespace MorningTrain\Laravel\Resources;


use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Resources\Console\CrudResourceMakeCommand;

class LaravelResourcesServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $resources = ResourceRepository::config();

        if (!empty($resources)) {
            foreach ($resources as $namespace => $resources) {
                foreach ($resources as $name => $resource) {
                    ResourceRepository::register($namespace, $name);
                }
            }
        }
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/resources.php' => config_path('resources.php'),
            ],
                'mt-config');

            $this->commands([
                CrudResourceMakeCommand::class,
            ]);
        }
    }

}
