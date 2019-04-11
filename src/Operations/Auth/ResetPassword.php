<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class ResetPassword extends Operation
{
    use ResetsPasswords;

    const ROUTE_METHOD = 'post';

    protected $middlewares = ['guest'];

    public function handle($model_or_collection = null)
    {
        return $this->reset(request());
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return [
            'user' => Auth::user(),
            'csrf' => csrf_token(),
        ];
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return ['errors' => [
            'email' => [trans($response)],
        ]];
    }
}
