<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

trait HasResourceView
{

    protected $_resource_view = null;

    protected function getResourceView()
    {
        return $this->_resource_view;
    }

    public function resourceView($view)
    {
        $this->_resource_view = $view;

        return $this;
    }

}
