<?php

namespace App\Jobs;

use App\Models\Claim;
use App\Services\ClaimManager;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClaimPurgerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param Claim $claimRepository
     * @param ClaimManager $claimManager
     *
     * @return void
     */
    public function handle(Claim $claimRepository, ClaimManager $claimManager)
    {
        $claimRepository->newModelQuery()
            ->where('claimed_at', '<', Carbon::now()->subSeconds(config('app.claim_max_age')))
            ->get()
            ->each(fn(Claim $claim) => $claimManager->forceDrop($claim->episode));
    }
}
