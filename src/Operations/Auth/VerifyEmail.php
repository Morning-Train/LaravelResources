<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;

use Illuminate\Foundation\Auth\VerifiesEmails;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class VerifyEmail extends Operation
{
    use VerifiesEmails;

    protected $middlewares = ['auth', 'signed', 'throttle:6,1'];

    public function handle($model_or_collection = null)
    {
        $response = $this->verify(request());
        $success  = $response->getSession()->get('verified') === true;

        return $response
            ->with('verification_status', $success ? 200 : 400)
            ->with('verification_message', $success ? $this->success_message : $this->error_message);
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

