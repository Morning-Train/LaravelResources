<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

trait HasMessage
{

    protected $success_message = '';

    public function successMessage($message = '')
    {
        $this->success_message = $message;

        return $this;
    }

    protected $error_message = '';

    public function errorMessage($message = '')
    {
        $this->error_message = $message;

        return $this;
    }

}
