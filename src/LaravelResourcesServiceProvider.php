<?php

namespace MorningTrain\Laravel\Resources;


use Illuminate\Support\ServiceProvider;
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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/resources.php' => config_path('resources.php'),
            ],
                'laravel-resources-config');

            $this->publishes([
                __DIR__ . '/Resources/SampleCrudResource.php' => app_path('Resources/SampleCrudResource.php'),
            ],
                'laravel-resources-resources');
        }

        $resources = config('resources');

        if (!empty($resources)) {
            foreach ($resources as $namespace => $resources) {
                foreach ($resources as $resource) {
                    ResourceRepository::register($namespace, $resource);
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
            $this->commands([
                CrudResourceMakeCommand::class,
            ]);
        }
    }

}
