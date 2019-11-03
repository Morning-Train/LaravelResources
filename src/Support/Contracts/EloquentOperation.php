<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\ResourceRepository;
use MorningTrain\Laravel\Resources\Support\Pipes\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\QueryToInstance;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;
use MorningTrain\Laravel\Resources\Support\Pipes\ValidatesFields;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

abstract class EloquentOperation extends Operation
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasModel;
    use HasFilters;

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function beforePipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters)->operation($this),
            QueryToInstance::create()->keyValue(request()->route()->parameter($this->getModelClassName()))->operation($this),
            TransformToView::create()->appends($this->appends),
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

    /////////////////////////////////
    /// Filters
    /////////////////////////////////

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

    public $single = false;

    public function single($value = true)
    {
        return $this->genericGetSet('single', $value);
    }

    /////////////////////////////////
    /// Routing
    /////////////////////////////////

    public function getRouteParameters()
    {
        return [
            $this->getModelClassName() => ['optional' => true]
        ];
    }

    /////////////////////////////////
    /// Model
    /////////////////////////////////

    public function onEmptyModel()
    {
        return null;
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
