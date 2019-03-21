<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class Index extends Operation
{

    const ROUTE_METHOD = 'get';

    public function isSingular()
    {
        return false;
    }

}
