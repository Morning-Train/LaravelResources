<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Pages;

use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;

/**
 * Class BladeView
 * @package MorningTrain\Laravel\Resources\Support\Pipes
 */
class BladeView extends Pipe
{

    /**
     * @var null
     */
    protected $path = null;

    /**
     * @param null $path
     * @return $this
     */
    public function path($path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param array $parameters
     * @return $this
     */
    public function parameters($parameters = [])
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     * @throws \Exception
     */
    public function pipe()
    {

        if ($this->path === null) {
            throw new \Exception('Tried to handle page operation, but no blade view name was supplied!');
        }

        return view($this->path)->with($this->parameters);
    }

}
