<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

trait HasRules
{

    public $rules = [];

    public function rules($rules = [])
    {
        $this->rules = $rules;

        return $this;
    }

    protected function hasRules()
    {
        return !empty($this->rules);
    }

}
