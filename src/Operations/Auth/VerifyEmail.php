<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;

use Illuminate\Foundation\Auth\VerifiesEmails;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class VerifyEmail extends Operation
{
    use VerifiesEmails;

    protected $middlewares = ['auth', 'signed', 'throttle:6,1'];

    public function handle()
    {
        $response = $this->verify(request());
        $success  = $response->getSession()->get('verified') === true;

        session()->push('flash_messages', [
            'type'    => $success ? 'success' : 'error',
            'message' => $success ? $this->success_message : $this->error_message,
        ]);

        return $response;
    }

    protected $redirectTo = null;

    public function setRedirectTo(string $route)
    {
        $this->redirectTo = $route;

        return $this;
    }

    public function getRoutePath()
    {
        return join('/',
            [
                $this->resource->getBasePath(),
                $this->name,
                "{id}",
                "{hash}",
            ]);
    }
}

