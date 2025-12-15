<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        
        $modelsPath = app_path('Models');
        $models = collect(\Illuminate\Support\Facades\File::allFiles($modelsPath))
            ->map(function ($file) {
                $class = 'App\\Models\\' . $file->getBasename('.php');
                return class_exists($class) && is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class) ? $class : null;
            })
            ->filter()
            ->values()
            ->toArray();
        foreach($models as $model){
            // dd($model);
        $model::observe(\App\Observers\ModelObserver::class);
        }
    }
}
