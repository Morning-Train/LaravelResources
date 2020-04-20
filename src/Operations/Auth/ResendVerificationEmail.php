<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;

use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\JsonResponse;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Traits\HasMessage;

class ResendVerificationEmail extends Operation
{
    use VerifiesEmails;
    use HasMessage;

    const ROUTE_METHOD = 'post';

    protected $middlewares = ['auth', 'throttle:6,1'];

    public function handle()
    {
        $success = $this->resend(request())->getSession()->get('resent') === true;

        $status_code = ($success ? 200 : 400);
        $message = ($success ? $this->success_message : $this->error_message);

        return new JsonResponse([
            'message' => $message
        ], $status_code);
    }
}
