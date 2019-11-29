<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class ForgotPassword extends Operation
{
    use SendsPasswordResetEmails;

    const ROUTE_METHOD = 'post';

    protected $middlewares = ['guest'];

    public function handle($model_or_collection = null)
    {
        return $this->sendResetLinkEmail(request());
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        $this->success(trans($response));
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        $this->badRequest(trans($response));
    }
}
