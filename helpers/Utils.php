<?php

use App\Models\Queue;

function lastQueueId()
{
    $lastQueueId = Queue::where('is_last_queue', true)->orderByDesc('created_at')->value('id');
    if (!$lastQueueId) {
        $lastQueueId = 0;
    }

    return $lastQueueId;
}

function formatDecimal($value)
{
    $value = str_replace(',', '.', $value);

    if (!is_numeric($value)) {
        throw new \InvalidArgumentException('Value must be a valid number.');
    }

    return $value;
}
