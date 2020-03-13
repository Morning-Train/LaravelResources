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

    public function pipe()
    {

        $query = $this->query;

        $data = null;

        if ($this->operation->expectsCollection()) {
            if($this->operation->single) {
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

        $this->operation->data = $data;

        $this->data = $data;
        
    }

}
