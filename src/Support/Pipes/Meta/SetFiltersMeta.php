<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
use Illuminate\Support\Arr;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;

class SetFiltersMeta extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasFilters;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle(Payload $payload, Closure $next)
    {
        $payload->set('meta.filters', $this->getFilterMeta());

        return $next($payload);
    }

}
