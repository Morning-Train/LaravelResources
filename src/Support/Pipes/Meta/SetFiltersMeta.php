<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
use Illuminate\Support\Arr;
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

    public function handle($payload, Closure $next)
    {

        if(is_array($payload)) {
            Arr::set($payload, 'meta.filters', $this->getFilterMeta());
        }

        return $next($payload);
    }

}
