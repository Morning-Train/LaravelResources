<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;

class Pipe
{

    public static $operation = null;

    public function operation()
    {
        return static::$operation;
    }

    public static function setOperation($operation)
    {
        static::$operation = $operation;
    }

    public function handle($content, Closure $next)
    {
        return $next($content);
    }

}
