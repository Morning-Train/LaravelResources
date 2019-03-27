<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use MorningTrain\Foundation\Api\Filter;
use MorningTrain\Laravel\Resources\Operations\Crud\Delete;
use MorningTrain\Laravel\Resources\Operations\Crud\Index;
use MorningTrain\Laravel\Resources\Operations\Crud\Read;
use MorningTrain\Laravel\Resources\Operations\Crud\Store;

abstract class CrudResource extends Resource
{

    protected static $model;

    public function operations()
    {
        return [
            Index::create()->model(static::$model)->filters($this->getFilters()),
            Read::create()->model(static::$model),
            Store::create()->model(static::$model)->fields($this->getFields()),
            Delete::create()->model(static::$model)
        ];
    }

    /////////////////////////////////
    // CRUD Helpers
    /////////////////////////////////

    protected function getFields()
    {
        return [];
    }

    protected function getFilters()
    {
        return [];
    }

}
