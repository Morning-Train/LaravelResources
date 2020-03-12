<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
use MorningTrain\Laravel\Resources\Support\Traits\Respondable;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

class Pipe
{

    protected $payload;

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use StaticCreate;
    use Respondable;

    public function handle(Payload $payload, Closure $next)
    {
        $this->payload = $payload;

        $this->pipe();

        return $next($payload);
    }

    protected function pipe()
    {

    }

    public function __get($name)
    {
        return $this->payload->{$name};
    }

    public function __set($name, $value)
    {
        $this->payload->{$name} = $value;
    }

}
