<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use MorningTrain\Laravel\Resources\Support\Pipes\Setup\SetupFilters;
use MorningTrain\Laravel\Resources\Support\Pipes\ToResourceView;
use MorningTrain\Laravel\Resources\Support\Traits\HasFields;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;
use MorningTrain\Laravel\Resources\Support\Traits\HasResourceView;

class EloquentOperation extends Operation
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasModel;
    use HasFilters;
    use HasFields;
    use HasResourceView;

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function setupPipes()
    {
        return [
            SetupFilters::create()->filters($this->filters),
        ];
    }

    protected function finallyPipes() {
        return [
            ToResourceView::create()->view($this->getResourceView())
        ];
    }

    /////////////////////////////////
    /// Routing
    /////////////////////////////////

    public function getRouteParameters()
    {

        $parameters = [];

        if($this->model !== null && class_exists($this->model)) {
            $parameters[$this->getModelClassName()] = ['optional' => true];
        }

        return $parameters;
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
                "required" => true,
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

    public function view($value = null)
    {
        $this->view = $value;

        return $this;
    }

    public function getView(string $val = null, $default = null)
    {
        $view = $this->view;

        return $val === null ?
            $view :
            $view[$val] ?? $default;
    }

}
