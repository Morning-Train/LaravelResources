<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Validation\UnauthorizedException;

class IsPermitted extends Pipe
{

    public function handle($data, Closure $next)
    {
        if (!$this->operation()->canExecute($data)) {
            throw new UnauthorizedException();
        }

        return $next($data);
    }

}
