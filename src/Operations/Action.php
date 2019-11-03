<?php

namespace MorningTrain\Laravel\Resources\Operations;

use MorningTrain\Laravel\Resources\Operations\Crud\Read;

class Action extends Read
{

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

    protected $trigger = null;

    public function trigger($value = null)
    {
        $this->trigger = $value;

        return $this;
    }

    public function performTrigger($model = null)
    {

        if ($model === null || $this->trigger === null) {
            return $model;
        }

        return $this->trigger instanceof \Closure ?
            $this->trigger($model) :
            $model->{$this->trigger}();
    }

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function pipes()
    {
        return array_merge(parent::pipes(), [
            function ($model, \Closure $next) {
                return $next($this->performTrigger($model));
            }
        ]);
    }

}

