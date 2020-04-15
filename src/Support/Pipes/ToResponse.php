<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\View\View;

class ToResponse extends Pipe
{

    protected function isResponseable($maybeResponse)
    {
        return $maybeResponse instanceof Response || $maybeResponse instanceof View;
    }

    protected function isException($maybeException)
    {
        return $maybeException instanceof \Exception;
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    protected function handleException(\Exception $exception)
    {
        throw $exception;
    }

    public function handle($payload, Closure $next)
    {

        if ($this->isException($payload)) {
            $this->handleException($payload);
        }

        if ($this->isResponseable($payload)) {
            return $next($payload);
        }

        if (!is_array($payload)) {
            return $next($payload);
        }

        $status = $this->operation()->getStatusCode();
        $headers = [];
        $options = 0;

        $payload['message'] = $this->operation()->getMessage();

        if (request()->has('_request_uuid')) {
            $payload['_request_uuid'] = request()->input('_request_uuid');
        }

        $response = response()->json($payload, $status, $headers, $options);

        return $next($response);
    }

}
