<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;
use MorningTrain\Laravel\Resources\Support\Traits\PipesPayload;

class SetModelSuccessMessage extends Pipe
{

    /////////////////////////////////
    /// Traits
    /////////////////////////////////

    use PipesPayload;

    /////////////////////////////////
    /// Handle
    /////////////////////////////////

    /**
     * @param $model
     * @param Closure $next
     * @return mixed
     */
    public function pipe()
    {

        $model = $this->data;

        if ($this->operation->success_message !== null) {
            if ($this->operation->success_message instanceof \Closure) {
                $this->operation->setMessage($this->operation->success_message($model));
            } else {
                $this->operation->setMessage($this->operation->success_message);
            }
        } else {
            $this->operation->setMessage(
                __('messages.model_saved_successfully',
                    [
                        'model' => trans_choice(
                            'models.' . get_class($model) . '.specified',
                            1
                        )
                    ])
            );
        }

    }

}
