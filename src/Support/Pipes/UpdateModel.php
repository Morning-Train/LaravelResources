<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Illuminate\Database\Eloquent\Model;
use Closure;
use MorningTrain\Laravel\Fields\Contracts\FieldContract;
use MorningTrain\Laravel\Resources\Support\Traits\HasFields;

class UpdateModel extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasFields;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($model, Closure $next)
    {
        if ($this->hasFields() && $model instanceof Model) {

            $request = request();

            if (is_array($this->fields) && !empty($this->fields)) {

                foreach ($this->fields as $field) {
                    $field->update($model, $request, FieldContract::BEFORE_SAVE);
                }

                $model->save();

                foreach ($this->fields as $field) {
                    $field->update($model, $request, FieldContract::AFTER_SAVE);
                }

            }
        }

        return $next($model);
    }

}
