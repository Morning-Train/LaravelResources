<?php

namespace MorningTrain\Laravel\Resources\Services;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
            $class = $this->config($namespace)[$resource];

            $this->resources->get($namespace)
                ->put($resource, new $class($namespace, $resource));
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
    /// Operations
    /////////////////////////////////

    protected $operations;

    /**
     * Returns a collection of all registered resource operations for the provided namespace
     *
     * @param string $namespace
     * @return Collection
     * @throws Exception
     */
    public function getOperations(string $namespace = null)
    {
        $closure = function (string $namespace) {
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
        };

        is_null($namespace) ?
            $this->forEachNamespace($closure) :
            $closure($namespace);

        return (is_null($namespace) ? $this->operations : $this->operations->get($namespace))->flatten();
    }

    /**
     * Returns a list of all operation identifiers for the provided namespace
     *
     * @param string $namespace
     * @return array
     * @throws Exception
     */
    public function getOperationIdentifiers(string $namespace = null)
    {
        return $this->getOperations($namespace)
            ->map->identifier()
            ->all();
    }

    /**
     * Returns a list of all restricted operation identifiers for the provided namespace
     *
     * @param string $namespace
     * @return array
     * @throws Exception
     */
    public function getRestrictedOperationIdentifiers(string $namespace = null)
    {
        return $this->getOperations($namespace)
            ->filter->restrict(null)
            ->map->identifier()
            ->values()
            ->all();
    }

    /**
     * Returns a list of all non-restricted operation identifiers for the provided namespace
     *
     * @param string $namespace
     * @return array
     * @throws Exception
     */
    public function getUnrestrictedOperationIdentifiers(string $namespace = null)
    {
        return $this->getOperations($namespace)
            ->reject->restrict(null)
            ->map->identifier()
            ->values()
            ->all();
    }

    /**
     * A list of all operations for the model
     *
     * @param string|object $model
     * @return array
     */
    public function getModelOperationIdentifiers($model)
    {
        $key = is_object($model) ? get_class($model) : $model;

        return data_get($this->getAllModelOperationIdentifiers(), $key, []);
    }

    /**
     * A list of all model operations in the system.
     *
     * @return array
     */
    public function getAllModelOperationIdentifiers()
    {
        return Cache::rememberForever('model_operations',
            function () {
                $operations = collect();
                $namespaces = array_keys(config('resources', []));

                foreach ($namespaces as $namespace) {
                    $this->getOperations($namespace)
                        ->flatten()
                        ->each(function ($operation) use ($operations) {
                            if (method_exists($operation, 'model')) {
                                $model      = $operation->model();
                                $identifier = $operation->identifier();

                                if($model === null) {
                                    return;
                                }

                                if (!$operations->has($model)) {
                                    $operations->put($model, collect());
                                }

                                $operations->get($model)->push($identifier);
                            }
                        });
                }

                return $operations->toArray();
            });
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

    protected function forEachNamespace(\Closure $closure)
    {
        $namespaces = $this->config();

        foreach ($namespaces as $namespace => $resources) {
            $closure($namespace, $resources);
        }
    }

    /////////////////////////////////
    /// Config helpers
    /////////////////////////////////

    protected $config;

    public function config(string $namespace = null)
    {
        if (!$this->config) {
            $config = config('resources', []);

            $this->config = array_map(function ($items) {
                return $this->dot($items);
            }, $config);
        }

        return Arr::get($this->config, $namespace);
    }

    protected function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $name => $value) {
            if (is_int($name)) { // Assumption that key was not set
                if (is_array($value)) {
                    throw new \Exception('Nested arrays of resources need to have a key.');
                }

                $name = Str::snake(class_basename($value));
            }

            $key = $prepend.$name;

            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, $this->dot($value, $key.'.'));
            } else {
                if (isset($results[$key])) {
                    throw new \Exception('Cannot have resources with duplicate name.');
                }

                $results[$key] = $value;
            }
        }

        return $results;
    }

}
