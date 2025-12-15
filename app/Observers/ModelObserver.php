<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ModelObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        //
        $this->logmodelEvent($model, 'created');
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        //
        $this->logmodelEvent($model, 'updated');
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        //
        $this->logmodelEvent($model, 'deleted');
    }
    public function deleting(Model $model): void
    {
        //
        $this->logmodelEvent($model, 'deleting');
    }
    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        //
            $this->logmodelEvent($model, 'restored');
    }

    /**
     * Handle the model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        //
        $this->logmodelEvent($model, 'force deleted');
    }

    /**
     * Helper method to log model events.
     */
    private function logmodelEvent(Model $model, string $event): void
    {
        Log::info("{$model->getTable()} - {$event}", [
            'model'   => get_class($model),
            'id'      => $model->id,
            'changes' => $model->getDirty(),      // only changed fields
            'data'    => $model->getAttributes(), // full model data
        ]);
        $this->cacheUsingEvent($model);
    }

    private function cacheUsingEvent(Model $model): void
    {
        $modelClass = get_class($model);
        Cache::put(class_basename($model), $modelClass::latest()->paginate(10), 3200);

    }
}
