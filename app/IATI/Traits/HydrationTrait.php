<?php

declare(strict_types=1);

namespace App\IATI\Traits;

use App\IATI\Models\Activity\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

trait HydrationTrait
{
    /**
     * @param array $activityData
     *
     * @return Collection|null
     */
    protected function hydrateTransactions(array $activityData): ?Collection
    {
        if (empty(Arr::get($activityData, 'transactions'))) {
            return null;
        }

        return LazyCollection::make(function () use ($activityData) {
            foreach (Arr::get($activityData, 'transactions', []) as $tx) {
                yield new Transaction(['transaction' => $tx]);
            }
        })->collect();
    }
}
