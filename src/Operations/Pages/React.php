<?php

namespace MorningTrain\Laravel\Resources\Operations\Pages;

class React extends Page
{

    protected $blade_view = 'pages.react';

    public function handle($model = null)
    {
        return parent::handle($model)->with('component', $this->component());
    }

    protected function getViewParameters()
    {
        return array_merge(
            parent::getViewParameters(),
            [
                'component' => $this->component()
            ]
        );
    }

    public function getPageEnvironment()
    {
        return array_merge(
            parent::getPageEnvironment(),
            [
                'component' => $this->resource()->namespace . '.' . $this->component(),
            ]
        );
    }

    public function component($value = null)
    {
        return $this->genericGetSet('component', $value);
    }

    /////////////////////////////////
    /// Exporting
    /////////////////////////////////

    public function export()
    {
        return array_merge(
            parent::export(),
            [
                "component" => $this->resource()->namespace . '.' . $this->component(),
            ]
        );
    }

}
