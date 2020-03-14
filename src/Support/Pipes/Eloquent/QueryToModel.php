<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class QueryToModel extends Pipe
{

    public function pipe()
    {

        if ($this->requires_instance === true) {
            $this->data = $this->query->firstOrFail();
        } else {
            $this->data = $this->query->first();
        }
    }

}
