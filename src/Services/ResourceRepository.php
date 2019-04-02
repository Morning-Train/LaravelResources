<?php

namespace MorningTrain\Laravel\Resources\Services;


use MorningTrain\Laravel\Context\Context;
use MorningTrain\Laravel\Resources\Support\Contracts\Resource;

class ResourceRepository
{

    protected $resources;

    public function __construct()
    {
        $this->resources = collect();
    }

    public function register($namespace, $resource)
    {
        $this->ensureNamespace($namespace);
        $this->resources->get($namespace)->put($resource, new $resource);
    }

    public function get($namespace, $resource)
    {
        $this->ensureNamespace($namespace);
        if (!$this->resources->get($namespace)->has($resource)) {
            $this->resources->get($namespace)->put($resource, new $resource);
        }
        return $this->resources->get($namespace)->get($resource);
    }

    public function ensureNamespace($namespace)
    {
        if (!$this->resources->has($namespace)) {
            $this->resources->put($namespace, collect());
        }
    }

    public function routes($namespace)
    {
        if (!$this->hasResources($namespace)) {
            return;
        }

        $this->boot($namespace);

        foreach ($this->getResources($namespace) as $resource) {
            $resource->routes();
        }
    }

    public function getResources($namespace)
    {
        $this->ensureNamespace($namespace);
        return $this->resources->get($namespace);
    }

    public function hasResources($namespace)
    {
        $this->ensureNamespace($namespace);
        return $this->getResources($namespace)->isNotEmpty();
    }

    /**
     * Returns a collection of all registered resource operations for the provided namespace
     *
     * @param string $namespace
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getOperations(string $namespace)
    {
        if (!$this->hasResources($namespace)) {
            return collect();
        }

        $this->boot($namespace);

        $operations = collect();

        /** @var Resource $resource */
        foreach ($this->getResources($namespace) as $resource) {
            $operations->put($resource::getName(), $resource->getOperations());
        }

        return $operations;
    }

    /**
     * Returns a list of all permission identifiers for the provided namespace
     *
     * @param string $namespace
     * @return array
     * @throws \Exception
     */
    public function getPermissions(string $namespace)
    {
        return $this->getOperations($namespace)->flatten()
            ->filter->restrict(null)
            ->map->identifier()
            ->all();
    }

    /**
     * Returns a list of all permission identifiers for all registered namespaces
     *
     * @return array
     * @throws \Exception
     */
    public function getAllPermissions()
    {
        $namespaces = array_keys(config('resources', []));
        $permissions = [];

        foreach ($namespaces as $namespace) {
            $permissions = array_merge($permissions, ResourceRepository::getPermissions($namespace));
        }

        return $permissions;
    }

    public function getModelKeyName()
    {
        return null;
    }

    public function getEmptyModelInstance()
    {
        return null;
    }

    public function boot($namespace)
    {

        //Boot individual resources
        if ($this->hasResources($namespace)) {
            foreach ($this->getResources($namespace) as $class => $resource) {
                $resource->boot($namespace);
            }

        }

    }

    public function export($namespace)
    {

        $environment_data = [];

        if ($this->hasResources($namespace)) {

            $environment_data[$namespace] = [];

            foreach ($this->getResources($namespace) as $class => $resource) {

                $resource->boot($namespace);

                $name = ($class)::getName();

                $environment_data[$namespace][$name] = [
                    "name" => $name,
                    "operations" => $resource->export()
                ];

            }

        }

        Context::localization()->provide('env', function () use ($environment_data) {
            return [
                'resources' => $environment_data
            ];
        });

    }

}
