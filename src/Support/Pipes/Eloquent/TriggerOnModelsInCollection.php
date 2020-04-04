<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class TriggerOnModelsInCollection extends Pipe
{

    protected $trigger = null;

    public function trigger($value = null)
    {
        $this->trigger = $value;

        return $this;
    }

    public function pipe()
    {

        $collection = $this->data;
        $trigger = $this->trigger;

        if ($collection instanceof Collection && $collection->isNotEmpty() && $trigger !== null) {
            foreach ($collection as &$model) {
                if ($model instanceof Model && $trigger !== null) {

                    $trigger instanceof \Closure ?
                        $trigger($model) :
                        $model->{$trigger}();

                }
            }
        }

        $this->data = $collection;

    }

}
