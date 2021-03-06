<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait Respondable
{

    /**
     * @param $message
     */
    protected function badRequest($message)
    {
        throw new BadRequestHttpException($message);
    }

    /**
     * @param $message
     */
    protected function forbidden($message)
    {
        throw new AccessDeniedHttpException($message);
    }

    /**
     * @param $message
     * @param int $statusCode
     */
    protected function success($message, int $statusCode = 200)
    {
        throw new HttpException($statusCode, $message);
    }
    
    /**
     * Throw a new response exception.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    protected function respond(Response $response) {
        throw new HttpResponseException($response);
    }

}
