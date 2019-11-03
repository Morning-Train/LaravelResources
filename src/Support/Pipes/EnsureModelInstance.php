<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class EnsureModelInstance extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasModel;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    /**
     * @param $model
     * @param Closure $next
     * @return mixed
     */
    public function handle($model, Closure $next)
    {

        if ($model === null) {
            if (class_exists($this->model)) {
                $model = new $this->model;
            }
        }

        return $next($model);
    }

}
