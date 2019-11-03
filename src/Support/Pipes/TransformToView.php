<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\UnauthorizedException;
use MorningTrain\Laravel\Filters\Filters\FilterCollection;

class TransformToView extends Pipe
{

    /////////////////////////////////
    /// Appends helpers
    /////////////////////////////////

    public $appends = null;

    public function appends($appends = null)
    {
        if ($appends !== null) {
            $this->appends = $appends;

            return $this;
        }
        return $this->appends;
    }

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($data, Closure $next)
    {

        $appends = $this->appends;

        if (is_array($appends)) {

            $appends = array_map('Str::snake', $appends);

            if ($data instanceof Model) {
                $data->setAppends($appends);
            }

            if ($data instanceof Collection) {
                $data->transform(function ($item) use ($appends) {
                    if ($item instanceof Model) {
                        $item->setAppends($appends);
                    }
                    return $item;
                });
            }

        }

        return $next($data);
    }

}
