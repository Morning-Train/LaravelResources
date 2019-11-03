<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

}
