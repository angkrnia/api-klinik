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
