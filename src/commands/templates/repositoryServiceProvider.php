<?php namespace Repositories\%Name%;

use Entities\%Name%;
use Repositories\%Name%\%Name%Repository;
use Illuminate\Support\ServiceProvider;

/**
* Register our Repository with Laravel
*/
class %Name%RepositoryServiceProvider extends ServiceProvider 
{
    public function register()
    {
        // Bind the returned class to the namespace 'Repositories\%Name%Interface
        $this->app->bind('Repositories\%Name%\%Name%Interface', function($app)
        {
            return new %Name%Repository(new %Name%());
        });
    }
}
