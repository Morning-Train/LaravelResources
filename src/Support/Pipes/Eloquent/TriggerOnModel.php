<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use Illuminate\Database\Eloquent\Model;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class TriggerOnModel extends Pipe
{

    protected $trigger = null;

    public function trigger($value = null)
    {
        $this->trigger = $value;

        return $this;
    }

    public function pipe()
    {

        $model = $this->data;
        $trigger = $this->trigger;

        if ($model instanceof Model && $trigger !== null) {

            $trigger instanceof \Closure ?
                $trigger($model) :
                $model->{$trigger}();

        }

        $this->data = $model;

    }

}
