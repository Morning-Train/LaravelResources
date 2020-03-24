<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;


use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class Logout extends Operation
{
    use AuthenticatesUsers;

    const ROUTE_METHOD = 'post';

    public function handle()
    {
        return $this->logout(request());
    }

    protected function loggedOut(Request $request)
    {
        return ['status' => true];
    }
}
