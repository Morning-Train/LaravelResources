<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class ConstrainQueryToKey extends Pipe
{

    use HasModel;

    public function pipe()
    {

        $keyValue = (request()->route() !== null)
            ? request()->route()->parameter($this->getModelClassName())
            : null;

        if ($keyValue !== null) {

            $query = $this->query;

            $query->whereKey($keyValue);

            $this->query = $query;
            $this->requires_instance = true;

        }

    }

}
