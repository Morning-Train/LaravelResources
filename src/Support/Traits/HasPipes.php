<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use Illuminate\Pipeline\Pipeline;

trait HasPipes
{

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
        return $this->pipes();
    }

    protected function executePipeline($payload)
    {
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
