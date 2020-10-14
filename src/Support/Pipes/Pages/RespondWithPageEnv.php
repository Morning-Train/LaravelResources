<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Pages;

use MorningTrain\Laravel\Context\Context;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class RespondWithPageEnv extends Pipe
{

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
