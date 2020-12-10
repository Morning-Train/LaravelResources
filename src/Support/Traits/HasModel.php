<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasModel
{

    /**
     * @var Model
     */
    public $model = null;
    public $appends = null;
    public $overwrite_appends = false;

    public function model($model = null)
    {
        $this->model = $model;

        return $this;
    }

    public function appends($appends = null, $overwrite = false)
    {
        $this->appends = $appends;
        $this->overwrite_appends = $overwrite;

        return $this;
    }

    public function hasModel()
    {
        return !!$this->model && (new $this->model instanceof Model);
    }

    public function newQueryFromModel()
    {
        return ($this->model)::query();
    }

    public function getModelKeyName()
    {
        $instance = $this->getEmptyModelInstance();

        if ($instance === null) {
            return null;
        }

        return $this->getEmptyModelInstance()->getKeyName();
    }

    public function getModelClassName()
    {
        return Str::snake(class_basename($this->model));
    }

    /**
     * @return Model
     */
    public function getEmptyModelInstance()
    {
        if (!class_exists($this->model)) {
            return null;
        }

        return new $this->model;
    }

}
