<?php

namespace MorningTrain\Laravel\Resources\Support\Contracts;

use MorningTrain\Laravel\Context\Context;

abstract class PageOperation extends Operation
{

    protected $blade_view = null;
    public $title = null;
    public $parent = null;

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
            'parent' => $this->parent(),
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


    public function parent($value = null)
    {
        return $this->genericGetSet('parent', $value);
    }

    public function getRoutePath()
    {
        return $this->path();
    }

    /////////////////////////////////
    /// Exporting
    /////////////////////////////////

    public function export()
    {
        return array_merge(
            parent::export(),
            $this->getPageEnvironment()
        );
    }

}
