<?php

namespace App\Resources;

use MorningTrain\Laravel\Resources\Support\Contracts\CrudResource;

class SampleCrudResource extends CrudResource
{
    protected static $model;

    protected static $operations = [
    ];

    protected function getFilters()
    {
        return [];
    }

    protected function getFields()
    {
        return [];
    }

}
