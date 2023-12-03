<?php

namespace Andriichello\Metrics;

use Laravel\Nova\Card;

class MetricsCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Metrics constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->height = 'auto';
    }

    /**
     * Set current restaurant id for the metrics card.
     *
     * @param int $id
     *
     * @return static
     */
    public function restaurant(int $id): static
    {
        $this->withMeta(['restaurant_id' => $id]);

        return $this;
    }

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component(): string
    {
        return 'metrics-card';
    }
}
