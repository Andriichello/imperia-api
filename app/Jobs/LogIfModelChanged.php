<?php

namespace App\Jobs;

use App\Models\BaseModel;
use App\Models\Interfaces\LoggableInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

/**
 * Class LogIfModelChanged.
 */
class LogIfModelChanged implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var BaseModel|LoggableInterface
     */
    protected BaseModel|LoggableInterface $model;

    /**
     * @var string
     */
    protected string $event;

    /**
     * Create a new job instance.
     *
     * @param BaseModel|LoggableInterface $model
     * @param string $event
     */
    public function __construct(BaseModel|LoggableInterface $model, string $event)
    {
        $this->model = $model;
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->model->logFieldsChanged()) {
            $attributes = Arr::only(
                $this->model->getAttributes(),
                $this->model->getLogFields(),
            );

            $this->model->logs()->create([
                'title' => $this->event,
                'metadata' => json_encode($attributes),
            ]);
        }
    }
}
