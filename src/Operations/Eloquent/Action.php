<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

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
        $trigger = $this->trigger;

        if ($model === null || $trigger === null) {
            return $model;
        }

        return $trigger instanceof \Closure ?
            $trigger($model) :
            $model->{$trigger}();
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

