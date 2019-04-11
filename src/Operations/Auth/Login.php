<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;


use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class Login extends Operation
{
    use AuthenticatesUsers;

    const ROUTE_METHOD = 'post';

    protected $middlewares = ['guest'];

    public function handle($model_or_collection = null)
    {
        return $this->login(request());
    }

    protected function authenticated(Request $request, $user)
    {
        return [
            'user' => $user,
            'csrf' => csrf_token(),
        ];
    }
}
