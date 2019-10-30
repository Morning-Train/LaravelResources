<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;

use Illuminate\Foundation\Auth\VerifiesEmails;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class ResendVerificationEmail extends Operation
{
    use VerifiesEmails;

    const ROUTE_METHOD = 'post';

    protected $middlewares = ['auth', 'throttle:6,1'];

    public function handle($model_or_collection = null)
    {
        $success = $this->resend(request())->getSession()->get('resent') === true;

        $this->setStatusCode($success ? 200 : 400);
        $this->setMessage($success ? $this->success_message : $this->error_message);

        return [];
    }
}
