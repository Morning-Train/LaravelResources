<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;

class QueryToInstance extends Pipe
{

    /////////////////////////////////
    /// KeyValue helpers
    /////////////////////////////////

    public $keyValue = null;

    public function keyValue($keyValue = null)
    {
        $this->keyValue = $keyValue;

        return $this;
    }

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle(Payload $payload, Closure $next)
    {

        $query = $payload->get('query');

        $data = null;

        if ($payload->operation->expectsCollection()) {
            if($payload->operation->single) {
                $data = $query->first();
            } else {
                $data = $query->get();
            }
        } else {
            if ($this->keyValue !== null) {
                $query->whereKey($this->keyValue);
                $data = $query->firstOrFail();
            }
        }

        $payload->operation->data = $data;

        $payload->set('data', $data);

        return $next($payload);
    }

}
