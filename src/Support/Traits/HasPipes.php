<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use Illuminate\Pipeline\Pipeline;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;

trait HasPipes
{


    protected $finally_pipes = [];

    public function finally($before_pipes = [])
    {
        $this->finally_pipes = $before_pipes;

        return $this;
    }

    protected function finallyPipes()
    {
        return [];
    }

    protected $before_pipes = [];

    public function before($before_pipes = [])
    {
        $this->before_pipes = $before_pipes;

        return $this;
    }

    protected $after_pipes = [];

    public function after($after_pipes = [])
    {
        $this->after_pipes = $after_pipes;

        return $this;
    }

    protected function setupPipes()
    {
        return [];
    }

    protected function beforePipes()
    {
        return [];
    }

    protected function afterPipes()
    {
        return [];
    }

    protected function authPipes()
    {
        return [];
    }

    protected function pipes()
    {
        return [];
    }

    protected function pipeline()
    {
        return app(Pipeline::class);
    }

    protected function buildPipes()
    {
        return array_merge(
            [
                function ($payload, $next) {
                    $payload = $next($payload);

                    $pipes = array_merge(
                        $this->finallyPipes(),
                        $this->finally_pipes
                    );

                    if (empty($pipes)) {
                        return $payload;
                    }

                    return $this->pipeline()
                        ->send($payload)
                        ->through($pipes)
                        ->thenReturn();
                }
            ],
            $this->setupPipes(),
            ($this->before_pipes instanceof \Closure) ? ($this->before_pipes)() : $this->before_pipes,
            $this->beforePipes(),
            $this->authPipes(),
            $this->pipes(),
            $this->afterPipes(),
            ($this->after_pipes instanceof \Closure) ? ($this->after_pipes)() : $this->after_pipes,
        );
    }

    public function execute($payload = null)
    {
        if ($payload === null) {
            $payload = new Payload($this);
        }

        $pipes = $this->buildPipes();

        if (empty($pipes)) {
            return $payload;
        }

        return $this->pipeline()
            ->send($payload)
            ->through($pipes)
            ->thenReturn();
    }

}
