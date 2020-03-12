<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
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

    public function handle(Payload $payload, Closure $next)
    {

        $response = $payload->data;

        if ($this->isException($response)) {
            $this->handleException($response);
        }

        if ($this->isResponseable($response)) {
            return $next($response);
        }

        if (!is_array($response)) {
            return $next($response);
        }

        $status = $payload->operation->getStatusCode();
        $headers = [];
        $options = 0;

        $response['message'] = $payload->operation->getMessage();

        $response = response()->json($response, $status, $headers, $options);

        return $next($response);
    }

}
