<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use Illuminate\Database\Eloquent\Model;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class EnsureModelInstance extends Pipe
{

    use HasModel;

    public function pipe()
    {

        $model = $this->data;

        if ($model === null || !($model instanceof Model)) {
            if (class_exists($this->model)) {
                $model = new $this->model;
                $this->data = $model;
            }
        }

    }

}
