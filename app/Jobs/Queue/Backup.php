<?php

namespace App\Jobs\Queue;

use App\Jobs\AsyncJob;
use Spatie\BackupTool\Jobs\CreateBackupJob;

/**
 * Class Backup.
 */
class Backup extends AsyncJob
{
    /**
     * Number of times the job should be tried.
     *
     * @var int
     */
    protected int $attempts = 1;

    /**
     * Determines if the database should be backed up.
     *
     * @var bool
     */
    protected bool $database;

    /**
     * Determines if the files should be backed up.
     *
     * @var bool
     */
    protected bool $files;

    /**
     * CalculateTotals constructor.
     *
     * @param bool $database
     * @param bool $files
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(bool $database = true, bool $files = false)
    {
        $this->database = $database;
        $this->files = $files;
    }

    /**
     * Get the option, which should be used
     * in CreateBackupJob creation.
     *
     * @return string
     */
    public function option(): string
    {
        if ($this->database && $this->files) {
            return '';
        }

        if ($this->database) {
            return 'only-db';
        }

        if ($this->files) {
            return 'only-files';
        }

        return '';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        (new CreateBackupJob($this->option()))->handle();
    }
}
