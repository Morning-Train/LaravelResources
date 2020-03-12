<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
use MorningTrain\Laravel\Resources\Support\Traits\Respondable;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

class Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use StaticCreate;
    use Respondable;

    public function handle(Payload $payload, Closure $next)
    {
        return $next($payload);
    }

}
