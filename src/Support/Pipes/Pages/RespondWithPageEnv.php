<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Pages;

use MorningTrain\Laravel\Context\Context;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\Respondable;

class RespondWithPageEnv extends Pipe
{

    use Respondable;

    public function pipe()
    {
        if(request()->expectsJson()) {
            $this->respond(
                response()->json(
                    Context::env()->data()
                )
            );
        }
    }

}
