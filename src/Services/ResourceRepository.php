<?php

namespace MorningTrain\Laravel\Resources\Services;


use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Context\Context;
use MorningTrain\Laravel\Resources\Support\Contracts\AdhocResource;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
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

            $resourceInstance = null;

            if(is_subclass_of($class, Operation::class)) {
                $resourceInstance = new AdhocResource($namespace, $resource);
                $resourceInstance->withOperation($class);
            } else {
                if(class_exists($class)) {
                    $resourceInstance = new $class($namespace, $resource);
                }
            }

            if($resourceInstance !== null) {
                $this->resources->get($namespace)->put($resource, $resourceInstance);
            }
        }
    }

    public function get(string $namespace, string $resource)
    {
        $this->register($namespace, $resource);

        return $this->resources->get($namespace)->get($resource);
    }

    public function getOperationForCurrentRoute()
    {
        if ($current_route = Route::getCurrentRoute()) {
            $resource_namespace = data_get($current_route->action, 'resource_namespace');
            $resource_name = data_get($current_route->action, 'resourceName');
            $operation_name = data_get($current_route->action, 'operationName');

            if(is_null($resource_namespace) || is_null($resource_name) || is_null($operation_name)) {
                return null;
            }

            $resource = $this->get($resource_namespace, $resource_name);

            if(empty($resource) || !isset($resource)) {
                return null;
            }

            $operation = $resource->operation($operation_name);

            return $operation;
        }

        return null;
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
                            ->put($resource->name, $resource->getBootedOperations());
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
        return $this->getOperations($namespace)->map->identifier;
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
                $namespaces = array_keys($this->getRegisteredOperations());

                foreach ($namespaces as $namespace) {
                    $this->getOperations($namespace)
                        ->flatten()
                        ->each(function ($operation) use ($operations) {
                            if (method_exists($operation, 'model')) {
                                $model      = $operation->model;
                                $identifier = $operation->identifier;

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

    protected function getExportForNamespace($namespace)
    {
        $environment_data = [];

        if ($this->hasResources($namespace)) {
            foreach ($this->getResources($namespace) as $resource) {
                Arr::set($environment_data, "{$namespace}.{$resource->name}", $resource->export());
            }

        }

        return $environment_data;
    }

    protected function getExportForCurrentOperation()
    {
        $operation = $this->getOperationForCurrentRoute();

        if($operation) {
            $environment_data = [];
            Arr::set($environment_data, "{$operation->getResource()->namespace}.{$operation->getResource()->name}.{$operation->name}", $operation->export());
            return $environment_data;
        }

        return [];
    }

    public function exportCurrentRoute()
    {
        $export = $this->getExportForCurrentOperation();

        Context::env(function () use ($export) {
            return [
                'resources' => $export,
            ];
        });
    }

    public function export(string $namespace)
    {

        if(config('app.env') === 'local') {
            $export = $this->getExportForNamespace($namespace);
        } else {
            $export = Cache::rememberForever('resources_export_for_' . $namespace, function () use ($namespace) {
                return $this->getExportForNamespace($namespace);
            });
        }

        Context::env(function () use ($export) {
            return [
                'resources' => $export,
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

    public function getRegisteredOperations()
    {
        $operations = config('resources', []);
        $namespaces_to_autoload = config('resources.settings.namespaces_to_autoload', []);

        if(!empty($namespaces_to_autoload)) {
            foreach($namespaces_to_autoload as $operations_namespace => $path) {

                $ucfirst_operations_namespace = Str::ucfirst($operations_namespace);

                $full_path = base_path("$path");

                $namespaced_path = Str::ucfirst(str_replace( '/', '\\', $path));
                $base_namespace = trim(implode('\\', array_slice(explode('\\', $namespaced_path), 0, -1)), '\\');

                $files = Storage::allFiles($full_path);

                $dir = new \RecursiveDirectoryIterator($full_path);
                $ite = new \RecursiveIteratorIterator($dir);
                $files = new \RegexIterator($ite, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:php|html)$#Di', \RegexIterator::GET_MATCH);
                $fileList = array();
                foreach($files as $file) {
                    if(is_array($file)) {
                        $file = $file[0];
                    }

                    $pathinfo = pathinfo($file);
                    $filename = $pathinfo['filename'];
                    $snaked_filename = Str::snake($filename);

                    $relative_path = trim(str_replace($full_path, '', $file), '/');
                    $namespaced_file_path = str_replace('/', '\\', $relative_path);
                    $path_fragments = array_slice(explode('\\', $namespaced_file_path), 0, -1);
                    $relavtive_namespace = trim(implode('\\', $path_fragments), '\\');

                    array_unshift($path_fragments, $operations_namespace);
                    array_push($path_fragments, $snaked_filename);

                    $dotted_path = Str::lower(implode('.', $path_fragments));

                    $namespace_fragments = array_filter(
                        [
                            $base_namespace,
                            $ucfirst_operations_namespace,
                            $relavtive_namespace,
                            $filename
                        ]
                    );
                    $full_namespace = implode('\\', $namespace_fragments);

                    if(class_exists($full_namespace)) {
                        data_set($operations, $dotted_path, $full_namespace);
                    }
                }

            }
        }

        return $operations;
    }

    public function config(string $namespace = null)
    {
        if (!$this->config) {
            $config = $this->getRegisteredOperations();

            if(isset($config['settings'])) {
                unset($config['settings']);
            }

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
