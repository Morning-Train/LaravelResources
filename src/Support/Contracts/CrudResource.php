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

    protected static $operations = [
        Index::class,
        Read::class,
        Store::class,
        Delete::class
    ];

    protected static $model;

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

    /////////////////////////////////
    // Action configuration
    /////////////////////////////////

    protected function configureIndexOperation(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

        // Configure the view to pull from the database
        $action->view([]);

        // Filters to apply to the query
        $action->filters($this->getFilters());

    }

    protected function configureReadOperation(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

    }

    protected function configureStoreOperation(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

        $action->fields($this->getFields());

    }

    protected function configureDeleteOperation(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

    }

}
