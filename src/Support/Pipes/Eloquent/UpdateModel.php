<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use Illuminate\Database\Eloquent\Model;
use MorningTrain\Laravel\Fields\Contracts\FieldContract;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasFields;

class UpdateModel extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasFields;

    /////////////////////////////////
    /// Pipe
    /////////////////////////////////

    public function pipe()
    {

        $model = $this->data;

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

        $this->data = $model;

    }

}
