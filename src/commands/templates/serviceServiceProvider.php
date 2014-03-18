<?php namespace Services\%Name%;

use Illuminate\Support\ServiceProvider;

/**
* Register our service with Laravel
*/
class %Name%ServiceServiceProvider extends ServiceProvider 
{
    /**
    * Registers the service in the IoC Container
    * 
    */
    public function register()
    {
        $this->app->bind('%Name%Service', function($app)
        {
            return new %Name%Service(
                // Inject in our class of %Name%Interface, this will be our repository
                $app->make('Repositories\%Name%\%Name%Interface')
            );
        });
    }
}
