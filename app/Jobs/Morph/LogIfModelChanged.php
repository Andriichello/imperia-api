<?php

namespace App\Jobs\Morph;

use App\Jobs\AsyncJob;
use App\Models\BaseModel;
use App\Models\Interfaces\LoggableInterface;
use Illuminate\Support\Arr;

/**
 * Class LogIfModelChanged.
 */
class LogIfModelChanged extends AsyncJob
{
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
