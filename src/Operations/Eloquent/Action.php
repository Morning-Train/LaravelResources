<?php

namespace MorningTrain\Laravel\Resources\Operations\Eloquent;

use MorningTrain\Laravel\Resources\Support\Pipes\Eloquent\TriggerOnModel;

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

    /////////////////////////////////
    /// Pipelines
    /////////////////////////////////

    protected function pipes()
    {
        return [
            TriggerOnModel::create()->trigger($this->trigger)
        ];
    }

}

