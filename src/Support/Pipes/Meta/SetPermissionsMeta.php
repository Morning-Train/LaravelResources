<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\ResourceRepository;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;

class SetPermissionsMeta extends Pipe
{

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

    protected function payloadToCollection($payload)
    {

        if($payload instanceof Model) {
            return collect([$payload]);
        }

        if($payload instanceof Collection) {
            return $payload;
        }

        if(is_array($payload) && isset($payload['collection'])) {
            return $this->payloadToCollection($payload['collection']);
        }

        if(is_array($payload) && isset($payload['model'])) {
            return $this->payloadToCollection($payload['model']);
        }

        return collect([$payload]);
    }

    protected function getPermissionsMeta($payload)
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        $collection = $this->payloadToCollection($payload);

        $res = $collection->mapWithKeys(function ($model) use ($user) {

            if ($model === null || !($model instanceof Model)) {
                return [];
            }

            return [
                $model->getKey() =>
                    collect(ResourceRepository::getModelPermissions($model))
                        ->filter(function ($operation) use ($model, $user) {
                            return $user->can($operation, $model);
                        })
                        ->values()
                        ->all(),
            ];
        });

        return $res;
    }

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    public function handle($payload, Closure $next)
    {

        $this->operation()->setMeta(['permissions' => $this->getPermissionsMeta($payload)]);

        return $next($payload);
    }

}
