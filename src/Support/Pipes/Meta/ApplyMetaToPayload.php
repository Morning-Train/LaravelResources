<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class ApplyMetaToPayload extends Pipe
{

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($payload, Closure $next)
    {
        if (is_array($payload)) {
            $payload['meta'] = $this->operation()->getMeta();
        }

        return $next($payload);
    }

}
