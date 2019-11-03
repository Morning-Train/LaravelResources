<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use MorningTrain\Laravel\Filters\Filters\FilterCollection;

trait HasFilters
{

    public $filters = null;

    public function filters($filters = null)
    {
        $this->filters = $filters;

        return $this;
    }

    public function hasFilters()
    {
        return is_array($this->filters) && !empty($this->filters);
    }

    public function applyFiltersToQuery(&$query)
    {
        FilterCollection::create($this->filters)->apply($query, request());
    }

}
