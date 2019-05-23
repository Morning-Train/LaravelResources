<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

        $this->data = $model_or_collection;
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
                $keys = $filter->getAllKeys();
                if (!empty($keys)) {
                    foreach ($keys as $key) {
                        $export[$key] = [
                            "key"   => $key,
                            "value" => $filter->getDefaultValue($key),
                        ];
                    }
                }
            }
        }

        if ($this->expectsCollection() === false) {

            $key = $this->resource()->base_name;

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
