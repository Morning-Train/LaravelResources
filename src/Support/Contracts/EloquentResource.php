<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use MorningTrain\Laravel\Resources\Operations\Eloquent\Delete;
use MorningTrain\Laravel\Resources\Operations\Eloquent\Index;
use MorningTrain\Laravel\Resources\Operations\Eloquent\Read;
use MorningTrain\Laravel\Resources\Operations\Eloquent\Store;

abstract class EloquentResource extends Resource
{

    protected $model;

    public static $operations = [
        Index::class,
        Read::class,
        Store::class,
        Delete::class,
    ];

    public function indexOperation(Index $index)
    {
        $index->model($this->model);
        $index->filters($this->getFilters());
    }

    public function readOperation(Read $read)
    {
        $read->model($this->model);
    }

    public function storeOperation(Store $store)
    {
        $store->model($this->model);
        $store->fields($this->getFields());
    }

    public function deleteOperation(Delete $delete)
    {
        $delete->model($this->model);
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
