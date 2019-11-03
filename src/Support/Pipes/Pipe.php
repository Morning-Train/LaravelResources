<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Traits\Respondable;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

class Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use StaticCreate;
    use Respondable;

    public $_operation = null;

    /**
     * @param Operation|null $operation
     * @return $this|null|Operation
     */
    public function operation(Operation $operation = null)
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
