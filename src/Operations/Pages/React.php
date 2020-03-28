<?php

namespace MorningTrain\Laravel\Resources\Operations\Pages;

use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class React extends Page
{

    protected $blade_view = 'pages.react';

    public function handle($model = null)
    {
        return parent::handle($model)->with('component', $this->component);
    }

    protected function getViewParameters()
    {
        return array_merge(
            parent::getViewParameters(),
            [
                'component' => $this->component
            ]
        );
    }

    public function getPageEnvironment()
    {
        return array_merge(
            parent::getPageEnvironment(),
            [
                'component' => $this->resource->namespace . '.' . $this->component,
            ]
        );
    }

    protected string $component;

    public function component($component = null): Operation
    {
        $this->component = $component;

        return $this;
    }

    /////////////////////////////////
    /// Exporting
    /////////////////////////////////

    public function export()
    {
        return array_merge(
            parent::export(),
            [
                "component" => $this->resource->namespace . '.' . $this->component,
            ]
        );
    }

}
