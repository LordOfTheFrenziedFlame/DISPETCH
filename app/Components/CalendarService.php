<?php

namespace App\Components;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class CalendarService
{
    public function groupByDate(Collection $models, string $dateField): \Illuminate\Support\Collection
    {
        return $models->groupBy(function ($model) use ($dateField) {
            $date = data_get($model, $dateField);
            return $date ? Carbon::parse($date)->format('Y-m-d') : 'no-date';
        });
    }
}
