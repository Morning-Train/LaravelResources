<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Illuminate\Database\Eloquent\Model;
use Closure;
use MorningTrain\Laravel\Fields\Contracts\FieldContract;

class UpdateModel extends Pipe
{

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

    protected function isUpdateable($data)
    {
        return $this->hasFields() && $data instanceof Model;
    }

    public function handle($model, Closure $next)
    {
        if ($this->isUpdateable($model)) {

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
