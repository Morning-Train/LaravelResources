<?php

namespace MorningTrain\Laravel\Resources\Operations\Pages;

use MorningTrain\Laravel\Resources\Support\Contracts\PageOperation;

class React extends PageOperation
{

    protected $blade_view = 'pages.react';

    public function handle($model = null)
    {
        return parent::handle($model)->with('component', $this->component());
    }

    public function getPageEnvironment()
    {
        return array_merge(
            parent::getPageEnvironment(),
            [
                'component' => $this->component()
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
                "component" => $this->component(),
            ]
        );
    }

}
