<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Fields\Traits\ValidatesFields;
use MorningTrain\Laravel\Filters\Filters\FilterCollection;
use MorningTrain\Laravel\Resources\ResourceRepository;

abstract class EloquentOperation extends Operation
{

    protected $single = false;

    public function single($value = true)
    {
        return $this->genericGetSet('single', $value);
    }

    public function prepare($parameters)
    {

        $model_or_collection = null;

        $key_value = null;
        if (is_array($parameters) && isset($parameters[0])) {
            $key_value = $parameters[0];
        }

        $query = $this->query();

        if ($this->expectsCollection()) {
            if($this->single) {
                $model_or_collection = $query->first();
            } else {
                $model_or_collection = $query->get();
            }
        } else {
            if ($key_value !== null) {
                $query->where($this->getModelKeyName(), '=', $key_value);
                $model_or_collection = $query->firstOrFail();
            } else {
                $model_or_collection = $this->onEmptyModel();
            }
        }

        $this->transformToView($model_or_collection);

        $this->data = $model_or_collection;
    }

    public function getRoutePath()
    {

        $route_fragments = [
            $this->resource->getBasePath(),
            $this->name,
        ];

        $model_class_name = $this->getModelClassName();

        if (!empty($model_class_name)) {
            array_push($route_fragments, "{" . $this->getModelClassName() . "?}");
        }

        return join('/', $route_fragments);
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
    /// Fields
    /////////////////////////////////

    use ValidatesFields;

    protected $fields = [];

    public function fields($value = null)
    {
        return $this->genericGetSet('fields', $value);
    }

    /////////////////////////////////
    /// Views
    /////////////////////////////////

    protected $view = [];
    protected $appends = false;

    public function view($value = null)
    {
        return $this->genericGetSet('view', $value);
    }

    public function appends($value = null)
    {
        return $this->genericGetSet('appends', $value);
    }

    public function getView(string $val = null, $default = null)
    {
        $view = $this->view;

        return $val === null ?
            $view :
            $view[$val] ?? $default;
    }

    public function transformToView(&$data)
    {

        $appends = $this->appends();

        if (is_array($appends)) {

            $appends = array_map('Str::snake', $appends);

            if ($data instanceof Model) {
                $data->setAppends($appends);
            }

            if ($data instanceof Collection) {
                $data->transform(function ($item) use ($appends) {
                    if ($item instanceof Model) {
                        $item->setAppends($appends);
                    }
                    return $item;
                });
            }

        }

    }

    public function constrainToView(Builder &$query)
    {
        $relations = $this->getView('with');
        $with      = [];

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

    /////////////////////////////////
    /// Filters
    /////////////////////////////////

    protected $filters = [];

    public function filters($value = null)
    {
        return $this->genericGetSet('filters', $value);
    }

    public function hasFilters()
    {
        return is_array($this->filters) && !empty($this->filters);
    }

    protected function exportFilters()
    {

        $export = [];

        if (!empty($this->filters)) {
            foreach ($this->filters as $filter) {
                $export = array_merge($export, $filter->export());
            }
        }

        if ($this->expectsCollection() === false) {

            $key = $this->getModelClassName();

            $export[$key] = [
                "key"   => $key,
                "value" => null,
            ];

        }

        return $export;
    }

    /////////////////////////////////
    /// Model
    /////////////////////////////////

    protected $model;

    public function model($value = null)
    {
        return $this->genericGetSet('model', $value);
    }

    public function getModelKeyName()
    {
        $instance = $this->getEmptyModelInstance();
        if ($instance === null) {
            return null;
        }
        return $this->getEmptyModelInstance()->getKeyName();
    }

    public function getModelClassName()
    {
        return Str::snake(class_basename($this->model));
    }

    public function getEmptyModelInstance()
    {
        if (!class_exists($this->model)) {
            return null;
        }
        return new $this->model;
    }

    public function onEmptyModel()
    {
        return null;
    }

    public function hasModel()
    {
        return !!$this->model && (new $this->model instanceof Model);
    }

    public function expectsCollection()
    {
        return false;
    }

    /////////////////////////////////
    /// Export
    /////////////////////////////////

    public function export()
    {
        return array_merge(
            parent::export(),
            [
                "model"   => $this->getModelClassName(),
                "key"     => $this->getModelKeyName(),
                "filters" => $this->exportFilters(),
            ]
        );
    }

    /////////////////////////////////
    /// Meta data for response payload
    /////////////////////////////////

    public function getMeta()
    {
        return array_merge(
            parent::getMeta(),
            [
                'filters'     => $this->getFilterMeta(),
                'permissions' => $this->getPermissionsMeta(),
            ]
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

    public function getPermissionsMeta()
    {
        if (!Auth::check()) {
            return [];
        }

        $user       = Auth::user();
        $data       = $this->data;
        $collection = $data instanceof Collection ? $data : collect([$data]);

        $res = $collection->mapWithKeys(function ($model) use ($user) {

            if($model === null || !($model instanceof Model)){
                return [];
            }

            return [$model->getKey() =>
                collect(ResourceRepository::getModelOperationIdentifiers($model))
                    ->filter(function ($operation) use ($model, $user) {
                        return $user->can($operation, $model);
                    })
                    ->values()
                    ->all(),
            ];
        });

        return $res;
    }

}
