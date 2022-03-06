<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Class MediaQueryBuilder.
 */
class MediaQueryBuilder extends EloquentBuilder
{
    /**
     * @param string $extension
     *
     * @return $this
     */
    public function withExtension(string $extension): static
    {
        $this->where('name', 'like', "%.$extension");

        return $this;
    }

    /**
     * @return $this
     */
    public function isPrivate(): static
    {
        $this->where('private', true);

        return $this;
    }

    /**
     * @return $this
     */
    public function isPublic(): static
    {
        $this->where('private', false);

        return $this;
    }
}
