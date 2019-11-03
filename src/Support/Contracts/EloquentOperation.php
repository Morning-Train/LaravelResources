<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use MorningTrain\Laravel\Resources\Support\Pipes\Meta\SetFiltersMeta;
use MorningTrain\Laravel\Resources\Support\Pipes\Meta\SetPermissionsMeta;
use MorningTrain\Laravel\Resources\Support\Pipes\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\QueryToInstance;
use MorningTrain\Laravel\Resources\Support\Pipes\ToPayload;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;
use MorningTrain\Laravel\Resources\Support\Traits\HasFields;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

abstract class EloquentOperation extends Operation
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasModel;
    use HasFilters;
    use HasFields;

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function beforePipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters)->operation($this),
            QueryToInstance::create()->keyValue(request()->route()->parameter($this->getModelClassName()))->operation($this),
            TransformToView::create()->appends($this->appends),
        ];
    }

    protected function afterPipes()
    {
        return [
            ToPayload::create(),
            SetFiltersMeta::create()->filters($this->filters),
            SetPermissionsMeta::create()
        ];
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
    /// Exporting
    /////////////////////////////////

    public function export()
    {
        return array_merge(
            parent::export(),
            [
                "model" => $this->getModelClassName(),
                "key" => $this->getModelKeyName(),
                "filters" => $this->exportFilters(),
            ]
        );
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
                "key" => $key,
                "value" => null,
            ];

        }

        return $export;
    }

    /////////////////////////////////
    /// TO BE DEPRECATED
    /////////////////////////////////

    public function expectsCollection()
    {
        return false;
    }

    public $single = false;

    public function single($value = true)
    {
        return $this->genericGetSet('single', $value);
    }

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

}
