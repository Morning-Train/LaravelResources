<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\UnauthorizedException;
use MorningTrain\Laravel\Filters\Filters\FilterCollection;

class QueryModel extends Pipe
{

    /////////////////////////////////
    /// Model helpers
    /////////////////////////////////

    public $model = null;

    public function model($model = null)
    {
        if ($model !== null) {
            $this->model = $model;

            return $this;
        }
        return $this->model;
    }

    public function hasModel()
    {
        return !!$this->model && (new $this->model instanceof Model);
    }

    /////////////////////////////////
    /// Filter helpers
    /////////////////////////////////

    public $filters = null;

    public function filters($filters = null)
    {
        if ($filters !== null) {
            $this->filters = $filters;

            return $this;
        }
        return $this->filters;
    }

    public function hasFilters()
    {
        return is_array($this->filters) && !empty($this->filters);
    }

    public function applyFiltersToQuery(&$query)
    {
        FilterCollection::create($this->filters)->apply($query, request());
    }

    /////////////////////////////////
    /// View helpers
    /////////////////////////////////

    public function constrainToView(Builder &$query)
    {
        $relations = $this->operation()->getView('with');
        $with      = [];

        if (is_array($relations)) {
            foreach ($relations as $key => $relation) {
                if (is_array($relation)) {
                    $relation = "{$key}:" . implode(',', $relation);
                }

                $with[] = $relation;
            }
        }

        return empty($with) ?
            $query :
            $query->with($with);
    }

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($data, Closure $next)
    {

        if (!$this->hasModel()) {
            throw new \Exception('No model available for query building in action');
        }

        $query = ($this->model)::query();

        if ($this->hasFilters()) {
            $this->applyFiltersToQuery($query);
        }

        if (!empty($this->operation()->getView('with'))) {
            $this->constrainToView($query);
        }

        return $next($query);
    }

}