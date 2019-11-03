<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;

class ApplyMetaToPayload extends Pipe
{

    public function handle($payload, Closure $next)
    {
        if (is_array($payload)) {
            $payload['meta'] = $this->operation()->getMeta();
        }

        return $next($payload);
    }

}
