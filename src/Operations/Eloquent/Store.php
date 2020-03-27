<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\ConstrainQueryToKey;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\EnsureModelInstance;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryToModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\UpdateModel;
use MorningTrain\Laravel\Resources\Support\Pipes\SetModelSuccessMessage;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;
use MorningTrain\Laravel\Resources\Support\Pipes\ValidatesFields;

class Store extends EloquentOperation
{
    const ROUTE_METHOD = 'post';

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function beforePipes()
    {
        return [
            QueryModel::create()->model($this->model)->filters($this->filters),
            ConstrainQueryToKey::create()->model($this->model),
            QueryToModel::create(),
            EnsureModelInstance::create()->model($this->model),
            TransformToView::create()->appends($this->appends),
        ];
    }

    protected function pipes()
    {
        return [
            ValidatesFields::create()->fields($this->fields),
            UpdateModel::create()->fields($this->fields)
        ];
    }

    protected function afterPipes()
    {
        return array_merge(parent::afterPipes(), [
            SetModelSuccessMessage::create()
        ]);
    }

}

