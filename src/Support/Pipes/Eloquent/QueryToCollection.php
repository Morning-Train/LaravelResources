<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class QueryToCollection extends Pipe
{

    public function pipe()
    {
        $this->data = $this->query->get();
    }

}
