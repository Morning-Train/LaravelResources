<?php

namespace MorningTrain\Laravel\Resources\Support\Traits;

use MorningTrain\Laravel\Filters\Filters\FilterCollection;

trait HasFilters
{

    public $filters = [];

    public function filters($filters = [], $merge = true)
    {
        if($merge === false) {
            $this->filters = $filters;
        } else {
            $this->filters = array_merge(
                $this->filters,
                $filters
            );
        }
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

    protected function getMetaForFilters($filters)
    {
        $export = [];

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $export = array_merge($export, $filter->getMetaData());
            }
        }

        return $export;
    }

    public function getFilterMeta()
    {
        return $this->getMetaForFilters($this->filters);
    }

}
