<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\ResourceRepository;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\PipesPayload;

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
