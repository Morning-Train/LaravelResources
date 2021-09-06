<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

trait HasFields
{

    public $fields = null;

    protected $_cached_fields = null;

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

    protected function getCachedFields()
    {
        if($this->_cached_fields === null) {
            $this->_cached_fields = $this->getFields();
        }

        return $this->_cached_fields;
    }

}
