<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\EnsureModelInstance;
use MorningTrain\Laravel\Resources\Support\Pipes\SetModelSuccessMessage;
use MorningTrain\Laravel\Resources\Support\Pipes\UpdateModel;
use MorningTrain\Laravel\Resources\Support\Pipes\ValidatesFields;

class Store extends EloquentOperation
{
    const ROUTE_METHOD = 'post';

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function pipes()
    {
        return [
            EnsureModelInstance::create()->model($this->model),
            ValidatesFields::create()->fields($this->fields),
            UpdateModel::create()->fields($this->fields)
        ];
    }

    protected function afterPipes()
    {
        return array_merge(parent::afterPipes(), [
            SetModelSuccessMessage::create()->operation($this)
        ]);
    }

}

