<?php

namespace MorningTrain\Laravel\Resources\Operations\Pages;

use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use MorningTrain\Laravel\Resources\Support\Pipes\Context\SetEnv;
use MorningTrain\Laravel\Resources\Support\Pipes\Pages\BladeView;
use MorningTrain\Laravel\Resources\Support\Pipes\Pages\RespondWithPageEnv;

/**
 * Class Page
 * @package MorningTrain\Laravel\Resources\Operations\Pages
 */
abstract class Page extends Operation
{

    protected $blade_view = null;

    /////////////////////////////////
    /// Setters
    /////////////////////////////////

    /**
     * @var null
     */
    protected $title = null;

    /**
     * @param $title
     * @return Operation
     */
    public function title($title): Operation
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @var null
     */
    protected $path = null;

    /**
     * @param $path
     * @return Operation
     */
    public function path($path): Operation
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @var null
     */
    protected $parent = null;

    /**
     * @var null
     */
    protected $parents = null;

    /**
     * @param $parent
     * @return Operation
     */
    public function parent($parent): Operation
    {
        $this->parent = $parent;

        return $this;
    }

    public function parents($parents)
    {
        $this->parents = $parents;

        return $this;
    }

    protected function getParents()
    {
        return $this->parents;
    }

    /**
     * @var null
     */
    protected $forceRedirect = null;

    /**
     * @param $forceRedirect
     * @return Operation
     */
    public function forceRedirect($forceRedirect): Operation
    {
        $this->forceRedirect = $forceRedirect;

        return $this;
    }

    /////////////////////////////////
    /// Pipeline
    /////////////////////////////////

    protected function pipes()
    {
        return [
            SetEnv::create()->environment(function(){
                return [
                    'page' => $this->getPageEnvironment()
                ];
            }),
            RespondWithPageEnv::create(),
            BladeView::create()->path($this->blade_view)->parameters($this->getViewParameters())
        ];
    }

    protected function getViewParameters()
    {
        return [
            'title' => $this->title
        ];
    }

    /////////////////////////////////
    /// Route helpers
    /////////////////////////////////

    public function getRoutePath()
    {
        return $this->path;
    }

    /////////////////////////////////
    /// Exporting & Environment
    /////////////////////////////////

    public function getPageEnvironment()
    {
        return [
            'title'         => $this->title,
            'path'          => $this->path,
            'route'         => $this->identifier,
            'parent'        => $this->parent,
            'parents'       => $this->getParents(),
            'namespace'     => $this->resource->namespace,
            'forceRedirect' => $this->forceRedirect ?? false
        ];
    }

    public function export()
    {
        return array_merge(
            parent::export(),
            $this->getPageEnvironment()
        );
    }

}
