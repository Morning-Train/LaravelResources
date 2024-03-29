<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class TransformToView extends Pipe
{

    use HasModel;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle(Payload $payload, Closure $next)
    {

        $data = $payload->get('data');

        $appends = $this->appends;

        if (is_array($appends)) {

            $appends = array_map([Str::class, 'snake'], $appends);

            if ($data instanceof Model) {
                if($this->overwrite_appends) {
                    $data->setAppends($appends);
                } else {
                    $data->append($appends);
                }
            }

            if ($data instanceof Collection) {
                $data->transform(function ($item) use ($appends) {
                    if ($item instanceof Model) {
                        if($this->overwrite_appends) {
                            $item->setAppends($appends);
                        } else {
                            $item->append($appends);
                        }
                    }
                    return $item;
                });
            }

        }

        $payload->set('data', $data);

        return $next($payload);
    }

}
