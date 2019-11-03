<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use MorningTrain\Laravel\Resources\Operations\Crud\Delete;
use MorningTrain\Laravel\Resources\Operations\Crud\Index;
use MorningTrain\Laravel\Resources\Operations\Crud\Read;
use MorningTrain\Laravel\Resources\Operations\Crud\Store;

abstract class CrudResource extends Resource
{

    protected $model;

    public function operations()
    {
        return [
            Index::create()->model($this->model)->filters($this->getFilters()),
            Read::create()->model($this->model),
            Store::create()->model($this->model)->fields($this->getFields()),
            Delete::create()->model($this->model)
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
