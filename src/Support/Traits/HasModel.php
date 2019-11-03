<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasModel
{

    public $model = null;

    public function model($model = null)
    {
        if ($model !== null) {
            $this->model = $model;

            return $this;
        }
        return $this->model;
    }

    public function hasModel()
    {
        return !!$this->model && (new $this->model instanceof Model);
    }

}
