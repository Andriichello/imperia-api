<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\Queue\QueueBackupRequest;
use App\Http\Requests\Queue\QueuePerformAlternationsRequest;
use App\Http\Responses\ApiResponse;
use App\Jobs\Morph\PerformAlternations;
use App\Jobs\Queue\Backup;
use App\Jobs\Version\ApplyDueVersions;
use Illuminate\Http\JsonResponse;
use Spatie\BackupTool\Jobs\CreateBackupJob;

/**
 * Class QueueController.
 */
class QueueController extends Controller
{
    /**
     * Queue database backup job.
     *
     * @param QueueBackupRequest $request
     *
     * @return JsonResponse
     */
    public function backup(QueueBackupRequest $request): JsonResponse
    {
        $this->dispatch(new Backup($request->isDb(), $request->isFiles()));

        return ApiResponse::make();
    }

    /**
     * Queue jobs for performing alterations (of the old menu) and applying scheduled versions.
     *
     * @param QueuePerformAlternationsRequest $request
     *
     * @return JsonResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function performAlternations(QueuePerformAlternationsRequest $request): JsonResponse
    {
        $this->dispatch(new PerformAlternations());
        $this->dispatch(new ApplyDueVersions());

        return ApiResponse::make();
    }
}
