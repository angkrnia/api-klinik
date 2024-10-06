<?php

namespace App\Console\Commands;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoResetAntrian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'antrian:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $latestQueue = Queue::latest()->first();
        $data = [
            'last_queue' => $latestQueue->queue,
            'status_queue' => $latestQueue->status,
            'created_at' => Carbon::now()->setTimezone('Asia/Jakarta')->toDateTimeString(),
        ];
        if ($latestQueue->status !== 'waiting' && $latestQueue->status !== 'on waiting') {
            $latestQueue->update(['is_last_queue' => true]);
            $data['status_jobs'] = 'SUCCESS';
        } else {
            $data['status_jobs'] = 'FAILED';
        }
        Log::info($data['status_jobs'] . " - " . date('Y-m-d H:i:s'));
        DB::table('jobs_auto_reset_antrian')->insert($data);
    }
}
