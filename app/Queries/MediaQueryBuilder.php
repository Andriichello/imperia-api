<?php

namespace App\Queries;

use Illuminate\Support\Str;

/**
 * Class MediaQueryBuilder.
 */
class MediaQueryBuilder extends BaseQueryBuilder
{
    /**
     * @param string $folder
     *
     * @return $this
     */
    public function fromFolder(string $folder): static
    {
        $folder = Str::of($folder)->start('/')->finish('/');
        $this->where('folder', $folder);

        return $this;
    }

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
