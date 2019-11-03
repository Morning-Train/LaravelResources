<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Illuminate\Database\Eloquent\Model;
use Closure;
use MorningTrain\Laravel\Fields\Traits\ValidatesFields as ValidatesFieldsTrait;

class ValidatesFields extends Pipe
{

    use ValidatesFieldsTrait;

    public $fields = null;

    public function fields($fields = null)
    {
        if ($fields !== null) {
            $this->fields = $fields;

            return $this;
        }
        return $this->fields;
    }

    protected function hasFields()
    {
        return !empty($this->fields);
    }

    protected function isValidateable($data)
    {
        return $this->hasFields() && $data instanceof Model;
    }

    public function handle($data, Closure $next)
    {
        if ($this->isValidateable($data)) {
            $this->performValidation($data, request());
        }

        return $next($data);
    }

}
