<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use MorningTrain\Laravel\Context\Context;

abstract class PageOperation extends Operation
{

    protected $blade_view = null;
    public $title = null;

    public function handle($model = null)
    {

        Context::localization()->provide('env', function () {
            return [
                'page' => $this->getPageEnvironment()
            ];
        });

        if($this->blade_view === null) {
            throw new \Exception('Tried to handle page operation, but no blade view name was supplied!');
        }

        return view($this->blade_view)->with('title', $this->title());
    }

    public function getPageEnvironment()
    {
        return [
            'title' => $this->title(),
            'path' => $this->path(),
            'route' => $this->identifier(),
        ];
    }

    public function path($value = null)
    {
        return $this->genericGetSet('path', $value);
    }

    public function title($value = null)
    {
        return $this->genericGetSet('title', $value);
    }

    public function getRoutePath()
    {
        return $this->path();
    }

}
