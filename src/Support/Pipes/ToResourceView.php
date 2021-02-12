<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ToResourceView extends Pipe
{

    protected $_view = null;

    public function view($view)
    {
        $this->_view = $view;

        return $this;
    }

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function pipe()
    {
        if($this->_view !== null) {

            $data = $this->data;

            if($data instanceof Collection) {
                $this->data = ['collection' => $this->_view::collection($data)->toArray(request())];
            } elseif($data instanceof Model) {
                $this->data = ['model' => (new $this->_view($data))->toArray(request())];
            }

        }

    }

}
