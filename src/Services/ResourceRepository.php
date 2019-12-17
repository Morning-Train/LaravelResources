<?php

namespace MorningTrain\Laravel\Resources\Services;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Access\Gate;
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

    public function routes(string $namespace, $options = [])
    {
        if (!$this->hasResources($namespace)) {
            return;
        }

        foreach ($this->getResources($namespace) as $resource) {
            $resource->routes($options);
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
     * @return Collection
     * @throws Exception
     */
    public function getOperationIdentifiers(string $namespace = null)
    {
        return $this->getOperations($namespace)->map->identifier();
    }

    /**
     * Return a list of all permissions tied to the model.
     *
     * @param $model
     * @return array
     */
    public function getModelPermissions($model)
    {
        $class = is_object($model) ? get_class($model) : $model;

        if (isset($class::$customPermissions)) {
            return array_merge(
                $class::$customPermissions,
                $this->getModelOperationIdentifiers($model)
            );
        }

        return $this->getModelOperationIdentifiers($model);
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
                                $model      = $operation->model;
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

    /**
     * Returns a list of operation/permission names
     * Which have policy methods NOT requiring an instance of the Model.
     * This is used to execute can() methods on f.ex. "create" operations
     * Which usually do not require a Model instance.
     *
     * @return mixed
     */
    public function getOperationPolicyParameters()
    {
        return Cache::rememberForever('policy_parameters', function () {
            $gate = app(Gate::class);

            return collect($this->getAllModelOperationIdentifiers())
                ->flatMap(function ($operations, $model) use ($gate) {
                    $policy = $gate->getPolicyFor($model);

                    if ($policy !== null) {
                        $policy = new \ReflectionClass(get_class($policy));
                    }

                    return collect($operations)
                        ->mapWithKeys(function ($operation) use ($model, $policy) {
                            $params = null;

                            if ($policy !== null) {
                                $parts = explode('.', $operation);
                                $name  = array_pop($parts);

                                // Here we assume that since the permission method only requires 1 parameter
                                // It does not require a Model instance.
                                if ($policy->hasMethod($name)
                                    && $policy->getMethod($name)->getNumberOfParameters() === 1) {
                                    $params = $model;
                                }
                            }

                            return [$operation => $params];
                        });
                })->filter();
        });
    }

    public function export(string $namespace)
    {

        $environment_data = [];

        if ($this->hasResources($namespace)) {
            foreach ($this->getResources($namespace) as $resource) {
                Arr::set($environment_data, "{$namespace}.{$resource->name}", $resource->export());
            }

        }

        Context::env(function () use ($environment_data) {
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
