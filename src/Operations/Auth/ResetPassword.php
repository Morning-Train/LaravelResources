<?php
namespace MorningTrain\Laravel\Resources\Operations\Auth;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Traits\HasMessage;
class ResetPassword extends Operation
{
    use ResetsPasswords;
    use HasMessage;
    const ROUTE_METHOD = 'post';
    protected $middlewares = ['guest'];
    public function handle()
    {
        return $this->reset(request());
    }
    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(
                $this->getResponseBody($response),
                200
            );
        }
        return redirect($this->redirectPath())
            ->with('status', trans($response));
    }
    /**
     * @param string $response
     * @return array
     */
    protected function getResponseBody(string $response): array
    {
        $body = [
            'message' => trans($response),
            'user'    => Auth::user(),
            'csrf'    => csrf_token(),
        ];
        return $body;
    }
}
