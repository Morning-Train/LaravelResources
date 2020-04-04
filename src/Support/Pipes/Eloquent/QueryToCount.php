<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class QueryToCount extends Pipe
{

    public function pipe()
    {
        $this->data = [
            'model' => [
                'count' => $this->query->count()
            ]
        ];
    }

}
