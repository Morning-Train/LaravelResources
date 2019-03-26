<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Traits\ValidatesFields;
use MorningTrain\Laravel\Filters\Filters\FilterCollection;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class Operation
{
    use StaticCreate, ValidatesFields;

    const ROUTE_METHOD = 'get';

    protected $model;
    protected $slug;
    protected $resource;
    protected $fields = [];
    protected $filters = [];
    protected $columns = [];
    protected $view = [];
    public $data = null;
    protected $restricted = false;

    public function __construct($resource, $slug)
    {
        $this->resource = $resource;
        $this->slug = $slug;
    }

    /////////////////////////////////
    /// Request helpers
    /////////////////////////////////

    public function prepare($parameters)
    {
        //
    }

    public function execute()
    {
        return new Payload($this, $this->handle($this->data));
    }

    public function handle($model_or_collection = null)
    {
        return $model_or_collection;
    }

    public function onEmptyResult()
    {
        return null;
    }

    /////////////////////////////////
    /// Basic helpers
    /////////////////////////////////

    public static function getName()
    {
        return Str::snake(class_basename(get_called_class()));
    }

    public function getModelKeyName()
    {
        $instance = $this->getEmptyModelInstance();
        if ($instance === null) {
            return null;
        }
        return $this->getEmptyModelInstance()->getKeyName();
    }

    public function getEmptyModelInstance()
    {
        if (!class_exists($this->model)) {
            return null;
        }
        return new $this->model;
    }

    /////////////////////////////////
    /// Getter/Setters
    /////////////////////////////////

    public function genericGetSet($name, $value = null)
    {
        if ($value === null) {
            return $this->{$name};
        }
        $this->{$name} = $value;
        return $this;
    }

    public function resource($value = null)
    {
        return $this->genericGetSet('resource', $value);
    }


    public function restrict($value = true)
    {
        return $this->genericGetSet('restricted', $value);
    }

    public function model($value = null)
    {
        return $this->genericGetSet('model', $value);
    }

    public function filters($value = null)
    {
        return $this->genericGetSet('filters', $value);
    }

    public function columns($value = null)
    {
        return $this->genericGetSet('columns', $value);
    }

    public function fields($value = null)
    {
        return $this->genericGetSet('fields', $value);
    }


    public function namespace($value = null)
    {
        return $this->genericGetSet('namespace', $value);
    }

    /////////////////////////////////
    /// Views
    /////////////////////////////////

    public function view($value = null)
    {
        return $this->genericGetSet('view', $value);
    }

    public function getView(string $val = null, $default = null)
    {
        $view = $this->view;

        return $val === null ?
            $view :
            $view[$val] ?? $default;
    }

    public function constrainToView(Builder &$query)
    {
        $relations = $this->getView('with');
        $with = [];

        if (is_array($relations)) {
            foreach ($relations as $key => $relation) {
                if (is_array($relation)) {
                    $relation = "{$key}:" . implode(',', $relation);
                }

                $with[] = $relation;
            }
        }

        return empty($with) ?
            $query :
            $query->with($with);
    }

    public function transformToView(string $name, $model)
    {
        $appends = static::getView($name, 'appends', []);
        $columns = static::getView($name, 'columns', []);
        $with = static::getView($name, 'with', []);
        $relations = [];

        foreach ($with as $key => $val) {
            $relations[] = is_array($val) ? $key : $val;
        }

        array_push($appends, 'permitted_actions');

        $only = array_merge($appends, $columns, $relations);

        $model = is_array($appends) ?
            $model->append($appends) :
            $model;

        return empty($columns) ?
            $model :
            $model->only($only);
    }

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

    public function hasModel()
    {
        return !!$this->model && (new $this->model instanceof Model);
    }

    public function hasFilters()
    {
        return is_array($this->filters) && !empty($this->filters);
    }

    public function isSingular()
    {
        return true;
    }

    public function getPermissionSlug()
    {
        return implode('.', [
            $this->namespace(),
            $this->resource()->name,
            $this->slug
        ]);
    }

    public function canExecute()
    {
        if (!$this->restricted) {
            return true;
        }

        if (Auth::check() && Auth::user()->can($this->getPermissionSlug())) {
            return true;
        }

        return false;
    }

    /////////////////////////////////
    /// Query
    /////////////////////////////////

    public function query()
    {

        if (!$this->hasModel()) {
            throw new \Exception('No model available for query building in action');
        }

        $query = ($this->model)::query();

        if ($this->hasFilters()) {
            $this->applyFiltersToQuery($query);
        }

        if (!empty($this->getView('with'))) {
            $this->constrainToView($query);
        }

        return $query;
    }

    public function applyFiltersToQuery(&$query)
    {
        FilterCollection::create($this->filters)->apply($query, request());
    }

    /////////////////////////////////
    /// Exporting
    /////////////////////////////////

    protected function exportFilters()
    {

        $export = [];

        if (!empty($this->filters)) {
            foreach ($this->filters as $filter) {
                $keys = $filter->getAllKeys();
                if (!empty($keys)) {
                    foreach ($keys as $key) {
                        $export[$key] = [
                            "key" => $key,
                            "value" => $filter->getDefaultValue($key)
                        ];
                    }
                }
            }
        }

        if ($this->isSingular()) {

            $key = $this->resource()->name;

            $export[$key] = [
                "key" => $key,
                "value" => null
            ];

        }

        return $export;
    }

    public function export()
    {

        $data = [];

        $data['name'] = static::getName();
        $data['key'] = $this->getModelKeyName();
        $data['filters'] = $this->exportFilters();

        return $data;
    }

    /////////////////////////////////
    /// Meta data for response payload
    /////////////////////////////////

    public function getMetaData()
    {
        return array_merge(
            [],
            $this->getFilterMeta()
        );
    }

    public function getFilterMeta()
    {

        $export = [];

        if (!empty($this->filters)) {
            foreach ($this->filters as $filter) {
                $export = array_merge($export, $filter->getMetaData());
            }
        }

        return $export;
    }

    /////////////////////////////////
    /// Routes
    /////////////////////////////////

    public function getControllerMethodName()
    {
        return static::getName() . 'Operation';
    }

    public function matchesControllerMethod($method_name)
    {
        return $this->getControllerMethodName() === $method_name;
    }

    public function getRoutePath()
    {

        $key = $this->resource->name;
        $route_path = Str::plural($this->resource->name) . '/' . $this->slug . "/{" . $key . "?}"; // TODO <- abstract getter on Operation

        return $route_path;
    }

    public function routes()
    {

        $route_group_props = ['operation' => $this->slug, 'resource_namespace' => $this->namespace()];

        $middlewares = [];

        if ($this->restricted) {
            $middlewares[] = 'permission:' . $this->getPermissionSlug();
        }

        if (!empty($middlewares)) {
            $route_group_props['middleware'] = $middlewares;
        }

        Route::group($route_group_props, function () {

            $route_name = $this->namespace() . '.resources.' . $this->resource->name . '.' . $this->slug;
            $route_path = $this->getRoutePath();
            $route_controller = '\\' . ResourceController::class . '@' . $this->getControllerMethodName();

            $route = Route::name($route_name);

            $callable = [$route, static::ROUTE_METHOD];

            if (is_callable($callable)) {
                call_user_func($callable, $route_path, $route_controller);
            }

        });

    }

}
