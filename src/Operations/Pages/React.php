<?php

namespace MorningTrain\Laravel\Resources\Operations\Pages;

use MorningTrain\Laravel\Resources\Support\Contracts\PageOperation;

class React extends PageOperation
{

    public function handle($model = null)
    {
        return view('pages.react')->with('component', $this->component());
    }

    public function component($value = null)
    {
        return $this->genericGetSet('component', $value);
    }

}
