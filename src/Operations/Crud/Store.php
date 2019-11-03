<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use Illuminate\Http\Request;
use MorningTrain\Laravel\Fields\Contracts\FieldContract;
use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\UpdateModel;

class Store extends EloquentOperation
{
    const ROUTE_METHOD = 'post';

    public function onEmptyModel()
    {
        return $this->getEmptyModelInstance();
    }

    public function handle($model = null)
    {
        if($this->success_message !== null) {
            if($this->success_message instanceof \Closure) {
                $this->setMessage($this->success_message($model));
            } else {
                $this->setMessage($this->success_message);
            }
        }
        else {
            $this->setMessage(
                __('messages.model_saved_successfully',
                ['model' => trans_choice(
                    'models.' . get_class($model) . '.specified',
                    1
                )])
            );
        }

        return $model;
    }

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function pipes()
    {
        return [
            UpdateModel::create()->fields($this->fields),
            function($data, \Closure $next){
                return $next($data);
            }
        ];
    }

}

