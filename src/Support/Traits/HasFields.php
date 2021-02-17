<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

trait HasFields
{

    public $fields = null;

    public function fields($fields = null)
    {
        $this->fields = $fields;

        return $this;
    }

    protected function hasFields()
    {
        return !empty($this->fields);
    }

    protected function getFields()
    {
        return $this->fields;
    }

}
