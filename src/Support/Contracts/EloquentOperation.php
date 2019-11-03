<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\ResourceRepository;
use MorningTrain\Laravel\Resources\Support\Pipes\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\ValidatesFields;

abstract class EloquentOperation extends Operation
{

    protected $single = false;

    public function single($value = true)
    {
        return $this->genericGetSet('single', $value);
    }

    public function prepare($query)
    {

        $data = null;

        $key_value = request()->route()->parameter($this->getModelClassName());

        if ($this->expectsCollection()) {
            if($this->single) {
                $data = $query->first();
            } else {
                $data = $query->get();
            }
        } else {
            if ($key_value !== null) {
                $query->whereKey($key_value);
                $data = $query->firstOrFail();
            } else {
                $data = $this->onEmptyModel();
            }
        }

        $this->transformToView($data);

        $this->data = $data;

        return $data;
    }

    public function getRouteParameters()
    {
        return [
            $this->getModelClassName() => ['optional' => true]
        ];
    }

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function initialPipes()
    {
        return array_merge([
            QueryModel::create()->model($this->model)->filters($this->filters)->operation($this)
        ], parent::initialPipes());
    }

    protected function beforePipes()
    {
        return [
            ValidatesFields::create()->fields($this->fields)
        ];
    }

    /////////////////////////////////
    /// Fields
    /////////////////////////////////

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
                collect(ResourceRepository::getModelPermissions($model))
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
