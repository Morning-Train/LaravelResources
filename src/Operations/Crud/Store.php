<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\EloquentOperation;
use MorningTrain\Laravel\Resources\Support\Pipes\EnsureModelInstance;
use MorningTrain\Laravel\Resources\Support\Pipes\UpdateModel;
use MorningTrain\Laravel\Resources\Support\Pipes\Validates;

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
            Validates::create()->fields($this->fields),
            UpdateModel::create()->fields($this->fields),
            function ($model, \Closure $next) {

                if ($this->success_message !== null) {
                    if ($this->success_message instanceof \Closure) {
                        $this->setMessage($this->success_message($model));
                    } else {
                        $this->setMessage($this->success_message);
                    }
                } else {
                    $this->setMessage(
                        __('messages.model_saved_successfully',
                            [
                                'model' => trans_choice(
                                    'models.' . get_class($model) . '.specified',
                                    1
                                )
                            ])
                    );
                }

                return $next($model);
            }
        ];
    }

}

