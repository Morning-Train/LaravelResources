<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use MorningTrain\Laravel\Fields\Contracts\FieldContract;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class Store extends Operation
{
    const ROUTE_METHOD = 'post';

    public function onEmptyResult()
    {
        return $this->getEmptyModelInstance();
    }

    public function handle(Model $model)
    {
        /** @var Request $request */
        $request = request();

        $this->performValidation($model, $request); // TODO patch value?

        if (is_array($this->fields) && !empty($this->fields)) {

            /** @var FieldContract $field */
            foreach ($this->fields as $field) {
                $field->update($model, $request, FieldContract::BEFORE_SAVE);
            }

            $model->save();

            foreach ($this->fields as $field) {
                $field->update($model, $request, FieldContract::AFTER_SAVE);
            }

        }

        return $model;
    }

}
