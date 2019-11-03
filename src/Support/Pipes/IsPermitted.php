<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;

class IsPermitted extends Pipe
{

    public function handle($data, Closure $next)
    {
        if (!$this->operation()->canExecute($data)) {
            $this->forbidden('Unable to perform operation');
        }

        return $next($data);
    }

}
