<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Messages;

use Closure;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

class Message extends Pipe
{

    public function __construct($message)
    {
        $this->_message = $message;
    }

    protected string $_message = '';

    /**
     * Translate the given message.
     *
     * @param  string|null  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return \Illuminate\Contracts\Translation\Translator|string|array|null
     */
    public function trans($key = null, $replace = [], $locale = null)
    {
        $this->_message = call_user_func_array('trans', func_get_args());

        return $this;
    }

    /**
     * Translates the given message based on a count.
     *
     * @param  string  $key
     * @param  \Countable|int|array  $number
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string
     */
    public function transChoice($key, $number, array $replace = [], $locale = null)
    {
        $this->_message = call_user_func_array('trans_choice', func_get_args());

        return $this;
    }

    /**
     * @param $model
     * @param Closure $next
     * @return mixed
     */
    public function pipe()
    {
        $this->message = $this->_message;
    }

}
