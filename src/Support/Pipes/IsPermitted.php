<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use MorningTrain\Laravel\Resources\ResourceRepository;

class IsPermitted extends Pipe
{

    public function pipe()
    {

        $data = $this->data;

        $data = $data instanceof Collection ?
            $data : collect([$data]);

        $operationIdentifier = $this->operation->identifier();

        $is_permitted = $data->every(function ($model) use($operationIdentifier) {
            return $this->isAllowed($operationIdentifier, $model);
        });

        if(!$is_permitted) {
            $this->forbidden('Unable to perform operation');
        }
    }

    protected function after()
    {

        $data = $this->data;

        if($data instanceof Model) {
            $data = new Collection([$data]);
        }

        if($data instanceof Collection) {
            $this->payload->set('meta.permissions', $this->getPermissionsMeta($data));
        }

    }

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

    protected $is_allowed_cache = [];

    protected function isAllowed($operationIdentifier, Model $model = null)
    {

        $cache_key = $operationIdentifier.(($model !== null)?$model->getKey().get_class($model):'');
        
        if(!isset($this->is_allowed_cache[$cache_key])) {
            $this->is_allowed_cache[$cache_key] = Gate::allows($operationIdentifier, $model);
        }
        return $this->is_allowed_cache[$cache_key];
    }

    protected function getPermissionsMeta($collection)
    {

        $res = $collection->mapWithKeys(function ($model) {

            if ($model === null || !($model instanceof Model)) {
                return [];
            }

            return [
                $model->getKey() =>
                    collect(ResourceRepository::getModelPermissions($model))
                        ->filter(function ($operationIdentifier) use ($model) {
                            return $this->isAllowed($operationIdentifier, $model);
                        })
                        ->values()
                        ->all(),
            ];
        });

        return $res;
    }

}
