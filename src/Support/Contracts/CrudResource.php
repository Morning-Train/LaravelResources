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

    protected function getColumns()
    {
        return [];
    }

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

    protected function configureIndexAction(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

        // Configure the view to pull from the database
        $action->view([]);

        // Columns to display in the index view
        $action->columns($this->getColumns());

        // Filters to apply to the query
        $action->filters($this->getFilters());

    }

    protected function configureReadAction(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

        // Columns to display in the read view
        $action->columns($this->getColumns());

    }

    protected function configureStoreAction(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

        $action->fields($this->getFields());

    }

    protected function configureDeleteAction(Operation $action)
    {

        //Set Eloquent model to be used by the action
        $action->model(static::$model);

    }

    /////////////////////////////////
    // Overrides
    /////////////////////////////////

    public function getModelKeyName()
    {
        return $this->getEmptyModelInstance()->getKeyName();
    }

    public function getEmptyModelInstance()
    {
        return new static::$model;
    }

}
