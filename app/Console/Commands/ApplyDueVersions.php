<?php

namespace App\Console\Commands;

use App\Repositories\Editor\VersionApplier;
use Illuminate\Console\Command;

/**
 * Class ApplyDueVersions.
 *
 * Applies the scheduled versions, whose time has come (the same as the job, which the
 * scheduler runs every minute, but right away).
 */
class ApplyDueVersions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'versions:apply-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply the scheduled versions, whose time has come';

    /**
     * Execute the console command.
     *
     * @param VersionApplier $applier
     *
     * @return int
     */
    public function handle(VersionApplier $applier): int
    {
        $counts = $applier->applyDue();

        $this->info("Applied {$counts['applied']}, failed {$counts['failed']}.");

        return $counts['failed'] ? self::FAILURE : self::SUCCESS;
    }
}
