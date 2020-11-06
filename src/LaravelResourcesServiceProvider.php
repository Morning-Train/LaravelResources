<?php

namespace MorningTrain\Laravel\Resources;


use Illuminate\Support\ServiceProvider;
use MorningTrain\Laravel\Context\Context;
use MorningTrain\Laravel\Context\Events\ContextsBooting;
use MorningTrain\Laravel\Resources\Console\EloquentResourceMakeCommand;
use Illuminate\Support\Facades\Event;

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
                EloquentResourceMakeCommand::class,
            ]);
        }

        Event::listen(ContextsBooting::class, function ($event) {

            Context::env([
                'settings' => [
                    'flatten_resources_export' => config('resources.settings.flatten_resources_export', false)
                 ]
            ]);

        });

    }

}
