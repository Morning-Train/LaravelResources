<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\ConstrainQueryToKey;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\EnsureModelInstance;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryToModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\UpdateModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Messages\ModelUpdatedMessage;
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
            QueryModel::create()->model($this->model)->filters($this->getCachedFilters()),
            ConstrainQueryToKey::create()->model($this->model),
            QueryToModel::create(),
            EnsureModelInstance::create()->model($this->model),
            TransformToView::create()->appends($this->appends, $this->overwrite_appends),
        ];
    }

    protected function pipes()
    {
        return [
            ValidatesFields::create()->fields($this->getFields()),
            UpdateModel::create()->fields($this->getFields()),
            ModelUpdatedMessage::create()
        ];
    }

}

