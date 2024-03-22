<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteUnconfirmedOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        //item_order will be cascade the delete action
        $deletedRowsCount = DB::table('orders')->where([
            ['status', '=', OrderStatus::Pending->value],
            ['updated_at', '<=', now()->subDays(2)],
        ])->delete();

        Log::info("Unconfirmed orders($deletedRowsCount) have been deleted by the scheduler.");
    }
}
