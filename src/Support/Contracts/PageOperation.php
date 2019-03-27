<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

abstract class PageOperation extends Operation
{

    public function path($value = null)
    {
        return $this->genericGetSet('path', $value);
    }

    public function getRoutePath()
    {
        return $this->path();
    }

}
