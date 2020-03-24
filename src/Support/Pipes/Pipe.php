<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
use MorningTrain\Laravel\Resources\Support\Traits\HasPipes;
use MorningTrain\Laravel\Resources\Support\Traits\Respondable;
use MorningTrain\Laravel\Support\Traits\StaticCreate;

class Pipe
{

    protected $payload;

    use StaticCreate;
    use Respondable;
    use HasPipes;

    /////////////////////////////////
    /// Handle is the main method to be executed in the pipeline
    /////////////////////////////////

    public function handle(Payload $payload, Closure $next)
    {

        /// If our pipe has a nested pipeline, execute it.
        /// If no additional pipes are defined, it will just return the payload back
        $payload = $this->executePipeline($payload);

        /// Set the payload for use in our pipe method
        $this->payload = $payload;

        /// Make the actual pipe task
        $maybe_response = $this->pipe();

        if($maybe_response) {
            $this->response = $maybe_response;
        }

        $this->payload = $next($payload);

        $this->after();

        return $this->payload;
    }

    /////////////////////////////////
    /// Life-cycle methods
    /////////////////////////////////

    protected function pipe()
    {
        /// Here we can do our work...
    }

    protected function after()
    {
        /// Handle work before we return the response
    }

    /////////////////////////////////
    /// Getter/Setter to proxy payload
    /////////////////////////////////

    public function __get($name)
    {
        return $this->payload->{$name};
    }

    public function __set($name, $value)
    {
        $this->payload->{$name} = $value;
    }

}
