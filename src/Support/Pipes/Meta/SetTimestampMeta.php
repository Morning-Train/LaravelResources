<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
use Illuminate\Support\Arr;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class SetTimestampMeta extends Pipe
{

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($payload, Closure $next)
    {

        if(is_array($payload)) {
            Arr::set($payload, 'meta.timestamp', now()->format('Y-m-d H:i:s O'));
        }

        return $next($payload);
    }

}
