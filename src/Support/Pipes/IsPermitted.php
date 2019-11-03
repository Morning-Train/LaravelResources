<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IsPermitted extends Pipe
{

    public function handle($data, Closure $next)
    {
        if (!$this->operation()->canExecute($data)) {
            throw new AccessDeniedHttpException('Unable to perform operation');
        }

        return $next($data);
    }

}
