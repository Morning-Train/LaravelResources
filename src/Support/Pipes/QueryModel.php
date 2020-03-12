<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\HasModel;

class QueryModel extends Pipe
{

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

    public function handle(Payload $payload, Closure $next)
    {

        if (!$this->hasModel()) {
            throw new \Exception('No model available for query building in action');
        }

        $query = $this->newQueryFromModel();

        if ($this->hasFilters()) {
            $this->applyFiltersToQuery($query);
        }

        if (!empty($payload->operation->getView('with'))) {
            $this->constrainToView($query);
        }

        $payload->set('query', $query);

        return $next($payload);
    }

}
