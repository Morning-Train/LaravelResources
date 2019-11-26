<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Collections;

use Closure;
use Illuminate\Support\Collection;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class KeyBy extends Pipe
{

    use HasModel;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($collection, Closure $next)
    {

        if($collection instanceof Collection) {
            $collection = $collection->keyBy($this->getModelKeyName());
        }

        return $next($collection);
    }

}
