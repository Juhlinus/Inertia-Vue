<?php

namespace InertiaVue;

use Illuminate\Support\ServiceProvider;

class InertiaVueServiceProvider extends ServiceProvider
{
    public function boot()
    {
    	if ($this->app->runningInConsole()) {
	        $this->commands([
	            InertiaVueCommand::class,
	        ]);
	    }
    }

    public function provides()
    {
        return [
            InertiaVueCommand::class,
        ];
    }
}