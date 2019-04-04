<?php

namespace MorningTrain\Laravel\Resources\Services;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MorningTrain\Laravel\Context\Context;
use MorningTrain\Laravel\Resources\Support\Contracts\Resource;

class ResourceRepository
{

    public function __construct()
    {
        $this->resources  = collect();
        $this->operations = collect();
    }

    /////////////////////////////////
    /// Resource Repository
    /////////////////////////////////

    protected $resources;

    public function register(string $namespace, string $resource)
    {
        $this->ensureNamespace($namespace);
        if (!$this->resources->get($namespace)->has($resource)) {
            $this->resources->get($namespace)
                ->put($resource, new $resource($namespace));
        }
    }

    public function get(string $namespace, string $resource)
    {
        $this->register($namespace, $resource);

        return $this->resources->get($namespace)->get($resource);
    }

    public function ensureNamespace(string $namespace)
    {
        if (!$this->resources->has($namespace)) {
            $this->resources->put($namespace, collect());
        }
    }

    /**
     * Returns a collection of all resources for the namespace
     *
     * @param string $namespace
     * @return Collection|Resource[]
     */
    public function getResources(string $namespace)
    {
        $this->ensureNamespace($namespace);

        return $this->resources->get($namespace);
    }

    public function routes(string $namespace)
    {
        if (!$this->hasResources($namespace)) {
            return;
        }

        foreach ($this->getResources($namespace) as $resource) {
            $resource->routes();
        }
    }

    /**
     * Check if provided namespace has any resources
     *
     * @param string $namespace
     * @return bool
     */
    public function hasResources(string $namespace): bool
    {
        $this->ensureNamespace($namespace);
        return $this->getResources($namespace)->isNotEmpty();
    }

    public function getModelKeyName()
    {
        return null;
    }

    public function getEmptyModelInstance()
    {
        return null;
    }

    /////////////////////////////////
    /// Operations and Permissions
    /////////////////////////////////

    protected $operations;

    /**
     * Returns a collection of all registered resource operations for the provided namespace
     *
     * @param string $namespace
     * @return Collection
     * @throws \Exception
     */
    public function getOperations(string $namespace)
    {
        // Ensure namespace exists
        if (!$this->operations->has($namespace)) {
            $this->operations->put($namespace, collect());
        }

        // Register operations
        if ($this->hasResources($namespace)) {
            foreach ($this->getResources($namespace) as $resource) {
                if (!$this->operations->get($namespace)->has($resource->name)) {
                    $this->operations->get($namespace)
                        ->put($resource->name, $resource->getOperations());
                }
            }
        }

        return $this->operations->get($namespace);
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
        $namespaces  = array_keys(config('resources', []));
        $permissions = [];

        foreach ($namespaces as $namespace) {
            $permissions = array_merge($permissions,
                $this->getPermissions($namespace));
        }

        return $permissions;
    }

    public function export(string $namespace)
    {

        $environment_data = [];

        if ($this->hasResources($namespace)) {

            $environment_data[$namespace] = [];

            foreach ($this->getResources($namespace) as $resource) {
                $environment_data[$namespace][$resource->name] = $resource->export();
            }

        }

        Context::localization()->provide('env',
            function () use ($environment_data) {
                return [
                    'resources' => $environment_data,
                ];
            });

    }

}
