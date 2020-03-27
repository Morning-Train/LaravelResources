<?php

namespace MorningTrain\Laravel\Resources\Support\Pipes;

use Closure;

class SetModelSuccessMessage extends Pipe
{

    /**
     * @param $model
     * @param Closure $next
     * @return mixed
     */
    public function pipe()
    {
        $this->message = (
            __('messages.model_saved_successfully',
                [
                    'model' => trans_choice(
                        'models.' . get_class($this->data) . '.specified',
                        1
                    )
                ])
        );
    }

}
