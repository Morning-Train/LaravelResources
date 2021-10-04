<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryToCollection;
use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\QueryModel;
use MorningTrain\Laravel\Resources\Support\Pipes\TransformToView;

class Index extends EloquentOperation
{

    const ROUTE_METHOD = 'get';

    protected function beforePipes()
    {
        return [

            /**
             * Takes filters and model name as parameters and returns a new query object
             */
            QueryModel::create()->model($this->model)->filters($this->getCachedFilters()),

            /**
             * Trigger `get` on the query and returns the resulting collection
             */
            QueryToCollection::create(),

            /**
             * Transform the collection by applying any appends to each model in the entry
             */
            TransformToView::create()->appends($this->appends, $this->overwrite_appends),
        ];
    }

}
