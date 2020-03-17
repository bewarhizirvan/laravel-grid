<?php

namespace BewarHizirvan\LaravelGrid\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelGrid extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravelgrid';
    }
}
