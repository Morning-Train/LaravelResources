<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Context;

use MorningTrain\Laravel\Context\Context;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class SetEnv extends Pipe
{

    /////////////////////////////////
    /// Setters
    /////////////////////////////////

    protected $environment = [];

    public function environment($environment = [])
    {
        $this->environment = $environment;

        return $this;
    }

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function pipe()
    {
        Context::env($this->environment);
    }

}
