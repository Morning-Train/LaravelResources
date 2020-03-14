<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

class IsPermitted extends Pipe
{

    public function pipe()
    {
        if (!$this->operation->canExecute($this->data)) {
            $this->forbidden('Unable to perform operation');
        }
    }

}
