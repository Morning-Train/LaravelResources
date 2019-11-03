<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

class Pipe
{

    use StaticCreate;

    public $_operation = null;

    public function operation($operation = null)
    {
        if ($operation !== null) {
            $this->_operation = $operation;

            return $this;
        }
        return $this->_operation;
    }

    public function handle($content, Closure $next)
    {
        return $next($content);
    }

}
