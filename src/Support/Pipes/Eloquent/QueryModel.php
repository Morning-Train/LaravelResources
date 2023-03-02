<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class QueryModel extends Pipe
{

    protected $is_filtering;

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use HasModel;
    use HasFilters;

    /////////////////////////////////
    /// View helpers
    /////////////////////////////////

    public function constrainToView(Builder &$query)
    {
        $relations = $this->operation->getView('with');
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

    public function pipe()
    {

        if (!$this->hasModel()) {
            throw new \Exception('No model available for query building in action');
        }

        $query = $this->newQueryFromModel();

        if ($this->hasFilters()) {
            $filterCollection = $this->applyFiltersToQuery($query);

            if(method_exists($filterCollection, 'isFiltering')) {
                $this->is_filtering = $filterCollection->isFiltering();
            } else {
                $this->is_filtering = $filterCollection->collection()->isNotEmpty();
            }

        }

        if (!empty($this->operation->getView('with'))) {
            $this->constrainToView($query);
        }

        $this->query = $query;
    }

    protected function after()
    {
        $this->payload->set('meta.state.is_filtering', $this->is_filtering);
    }

}
