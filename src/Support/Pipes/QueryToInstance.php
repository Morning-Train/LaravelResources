<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;

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

    public function handle($query, Closure $next)
    {

        $data = null;

        if ($this->operation()->expectsCollection()) {
            if($this->operation()->single) {
                $data = $query->first();
            } else {
                $data = $query->get();
            }
        } else {
            if ($this->keyValue !== null) {
                $query->whereKey($this->keyValue);
                $data = $query->firstOrFail();
            } else {
                $data = $this->operation()->onEmptyModel();
            }
        }

        $this->operation()->data = $data;

        return $next($data);
    }

}
