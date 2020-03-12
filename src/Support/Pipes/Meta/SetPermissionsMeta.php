<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes\Meta;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MorningTrain\Laravel\Resources\ResourceRepository;
use MorningTrain\Laravel\Resources\Support\Contracts\Payload;
use MorningTrain\Laravel\Resources\Support\Pipes\Pipe;
use MorningTrain\Laravel\Resources\Support\Traits\HasFilters;
use MorningTrain\Laravel\Resources\Support\Traits\PipesPayload;

class SetPermissionsMeta extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use PipesPayload;

    /////////////////////////////////
    /// Helpers
    /////////////////////////////////

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

    public function handle(Payload $payload, Closure $next)
    {
        $payload->set('meta.permissions', $this->getPermissionsMeta($payload->data));

        return $next($payload);
    }

}
