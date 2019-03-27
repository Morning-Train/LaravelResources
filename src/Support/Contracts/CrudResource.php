<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use MorningTrain\Laravel\Resources\Operations\Crud\Delete;
use MorningTrain\Laravel\Resources\Operations\Crud\Index;
use MorningTrain\Laravel\Resources\Operations\Crud\Read;
use MorningTrain\Laravel\Resources\Operations\Crud\Store;

abstract class CrudResource extends Resource
{

    protected $model;
    protected $restricted = true;

    public function operations()
    {
        return [
            Index::create()->model($this->model)->restrict($this->restricted)->filters($this->getFilters()),
            Read::create()->model($this->model)->restrict($this->restricted),
            Store::create()->model($this->model)->restrict($this->restricted)->fields($this->getFields()),
            Delete::create()->model($this->model)->restrict($this->restricted)
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
