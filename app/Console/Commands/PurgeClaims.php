<?php

namespace App\Console\Commands;

use App\Models\Claim;
use App\Services\ClaimManager;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgeClaims extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claims:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove any claims on episodes, that have passed their EOL';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Claim $claimRepository
     * @param ClaimManager $claimManager
     *
     * @return int
     */
    public function handle(Claim $claimRepository, ClaimManager $claimManager)
    {
        // load claims, that are overdue
        $claims = $claimRepository->newModelQuery()
            ->where('claimed_at', '<', Carbon::now()->subSeconds(config('app.claim_max_age')))
            ->get();

        // notify user how many claims are purged
        if ($this->output->isVerbose())
            $this->output->writeln('<comment>Purging ' . $claims->count() . ' Claims</comment>');

        // purge all overdue claims
        $claims->each(fn(Claim $claim) => $claimManager->forceDrop($claim->episode));

        return 0;
    }
}
