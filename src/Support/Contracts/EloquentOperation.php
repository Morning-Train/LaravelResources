<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Traits\ValidatesFields;
use MorningTrain\Laravel\Filters\Filters\FilterCollection;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class EloquentOperation extends Operation
{

    public function prepare($parameters)
    {

        $model_or_collection = null;

        $key_value = null;
        if (is_array($parameters) && isset($parameters[0])) {
            $key_value = $parameters[0];
        }

        $query = $this->query();

        if ($this->expectsCollection()) {
            $model_or_collection = $query->get();
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
                            "key" => $key,
                            "value" => $filter->getDefaultValue($key)
                        ];
                    }
                }
            }
        }

        if ($this->expectsCollection() === false) {

            $key = $this->resource()->name;

            $export[$key] = [
                "key" => $key,
                "value" => null
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
                "key" => $this->getModelKeyName(),
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

}
