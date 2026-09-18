<?php

namespace App\Observers;

use App\Services\RecapService;
use Illuminate\Support\Facades\Log;

class RecapObserver
{
    public function __construct(protected RecapService $recapService) {}

    /**
     * Handle the "created" event for any model.
     */
    public function created($model): void
    {
        $this->invalidateCache('created', $model);
    }

    /**
     * Handle the "updated" event for any model.
     */
    public function updated($model): void
    {
        $this->invalidateCache('updated', $model);
    }

    /**
     * Handle the "deleted" event for any model.
     */
    public function deleted($model): void
    {
        $this->invalidateCache('deleted', $model);
    }

    /**
     * Invalidate recap cache.
     */
    protected function invalidateCache(string $event, $model): void
    {
        $this->recapService->invalidateCache();

        $modelClass = get_class($model);

        Log::info('RecapObserver: Cache invalidated', [
            'event' => $event,
            'model' => $modelClass,
            'model_id' => $model->id,
            'facility_id' => $model->facility_id ?? null,
        ]);
    }
}
