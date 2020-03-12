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
        $data = $payload->data;

        if(is_array($data)) {
            Arr::set($data, 'meta.filters', $this->getFilterMeta());
        }

        $payload->data = $data;

        return $next($payload);
    }

}
