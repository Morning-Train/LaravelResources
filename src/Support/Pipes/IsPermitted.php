<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;

class IsPermitted extends Pipe
{

    public function handle(Payload $payload, Closure $next)
    {

        if (!$payload->operation->canExecute($payload->data)) {
            $this->forbidden('Unable to perform operation');
        }

        return $next($payload);
    }

}
