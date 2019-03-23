<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Fields\Traits\ValidatesFields;
use MorningTrain\Laravel\Filters\Filters\FilterCollection;
use MorningTrain\Laravel\Resources\Http\Controllers\ResourceController;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

abstract class EloquentOperation extends Operation
{

    public function prepare($parameters)
    {

        $model_or_collection = null;

        $key_value = null;
        if (is_array($parameters) && isset($parameters[0])) {
            $key_value = $parameters[0];
        }

        $query = $this->query();

        if ($this->isSingular()) {

            if ($key_value !== null) {
                $query->where($this->getModelKeyName(), '=', $key_value);
                $model_or_collection = $query->firstOrFail();
            } else {
                $model_or_collection = $this->onEmptyResult();
            }

        } else {
            $model_or_collection = $query->get();
        }

        $this->data = $model_or_collection;
    }

}
