<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
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

        $this->operation()->setMeta(['filters' => $this->getFilterMeta()]);

        return $next($payload);
    }

}
