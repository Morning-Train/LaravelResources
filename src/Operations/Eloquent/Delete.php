<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\FetchesModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\TriggerOnModel;

class Delete extends EloquentOperation
{

    public const ROUTE_METHOD = 'delete';

    protected function beforePipes(): array
    {
        return [
            FetchesModel::create()
                ->model($this->model)
                ->filters($this->getCachedFilters())
                ->appends($this->appends),
        ];
    }


    protected function pipes(): array
    {
        return [
            TriggerOnModel::create()->trigger('delete'),
        ];
    }

    public function getRouteParameters(): array
    {
        $parameters = [];

        if($this->model !== null && class_exists($this->model)) {
            $parameters[$this->getModelClassName()] = ['optional' => false];
        }

        return $parameters;
    }
}
