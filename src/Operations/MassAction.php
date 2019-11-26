<?php

namespace App\Operations;

use Illuminate\Support\Collection;
use MorningTrain\Laravel\Filters\Filters\Filter;
use MorningTrain\Laravel\Resources\Operations\Action;
use MorningTrain\Laravel\Resources\Support\Pipes\Collections\KeyBy;
use MorningTrain\Laravel\Resources\Support\Pipes\Validates;
use MorningTrain\Laravel\Resources\Support\Traits\HasRules;

class MassAction extends Action
{

    use HasRules;

    const ROUTE_METHOD = 'post';

    public function __construct()
    {

        $this->filters([
            Filter::create()->when('ids', function ($q, $ids) {
                $q->whereIn($this->getModelKeyName(), $ids);
            })->missing('ids', function ($q) {
                abort(401, 'Do not be stupid!');
            })
        ]);

        $this->rules([
            'ids' => 'required|array'
        ]);

    }

    public function beforePipes()
    {
        return array_merge(
            [
                Validates::create()->rules($this->rules)
            ],
            parent::beforePipes(),
            [
                KeyBy::create()->model($this->model)
            ]
        );
    }

    public function expectsCollection()
    {
        return true;
    }

    public function performTrigger($collection = null)
    {

        $trigger = $this->trigger;

        if ($collection === null || !($collection instanceof Collection)) {
            abort(401, 'Unable to perform mass action - Did not get expected Collection');
        }

        if ($collection->isEmpty()) {
            return abort(401, 'No entries found to apply mass action to');
        }

        $collection->transform(function ($model) use ($trigger) {
            return $trigger instanceof \Closure ?
                $trigger($model) :
                $model->{$trigger}();
        });

        return $collection;
    }
}