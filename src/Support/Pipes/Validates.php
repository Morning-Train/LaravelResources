<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Foundation\Validation\ValidatesRequests;
use MorningTrain\Laravel\Resources\Support\Traits\HasRules;

class Validates extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use ValidatesRequests;
    use HasRules;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($data, Closure $next)
    {

        if(!empty($this->rules)) {
            $this->validate(request(), $this->rules);
        }

        return $next($data);
    }

}
