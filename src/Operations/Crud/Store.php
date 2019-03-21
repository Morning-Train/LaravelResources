<?php

namespace MorningTrain\Laravel\Resources\Operations\Crud;

use MorningTrain\Foundation\Api\Field;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;

class Store extends Operation
{

    const ROUTE_METHOD = 'post';

    public function onEmptyResult()
    {
        return $this->getEmptyModelInstance();
    }

    public function handle($model)
    {

        $request = request();

        $this->performValidation($model, $request);

        if (is_array($this->fields) && !empty($this->fields)) {

            foreach ($this->fields as $field) {
                $field->update($model, $request, Field::BEFORE_SAVE);
            }

            $model->save();

            foreach ($this->fields as $field) {
                $field->update($model, $request, Field::AFTER_SAVE);
            }

        }

        return $model;
    }

    // MOVE to Trait in Fields Package
    protected function performValidation($model, $request, $patch = false)
    {
        // Compute validation rules
        $rules = [];

        foreach ($this->fields as $field) {
            $rule = $field->getValidationRules($model, $request);

            if (is_array($rule)) {
                $rules = array_merge($rules, $rule);
            }
        }

        // Convert validation rules if patch request
        if ($patch) {
            $rules = $this->getPatchValidationRules($rules);
        }

        // Validate
        $request->validate($rules);
    }

    protected function getPatchValidationRules(array $rules)
    {
        $patch_rules = [];

        foreach ($rules as $prop => $rule) {
            $patch_rules[$prop] = "sometimes|$rule";
        }

        return $patch_rules;
    }

}
