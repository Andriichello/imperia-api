<?php

namespace App\Jobs\Queue;

use App\Jobs\AsyncJob;
use Exception;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;
use Spatie\BackupTool\Jobs\CreateBackupJob;

/**
 * Class Backup.
 */
class Backup extends AsyncJob
{
    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    protected array $backoff = [30, 45, 60];

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
     * @throws Exception
     */
    public function handle(): void
    {
        $option = $this->option();

        $job = BackupJobFactory::createFromArray(config('backup'));

        if ($option === 'only-db') {
            $job->dontBackupFilesystem();
        }

        if ($option === 'only-files') {
            $job->dontBackupDatabases();
        }

        if (!empty($option)) {
            $prefix = str_replace('_', '-', $option) . '-';

            $job->setFilename($prefix . date('Y-m-d-H-i-s') . '.zip');
        }

        $job->disableNotifications();

        $job->run();
    }
}
