<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use MorningTrain\Laravel\Fields\Traits\ValidatesFields as ValidatesFieldsTrait;
use MorningTrain\Laravel\Resources\Support\Traits\HasFields;

class ValidatesFields extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use ValidatesFieldsTrait;
    use HasFields;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function pipe()
    {

        $data = $this->data;

        if ($this->hasFields() && $data instanceof Model) {
            $this->performValidation($data, request());
        }

    }

}
