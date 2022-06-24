<?php

namespace App\Queries;

/**
 * Class MediaQueryBuilder.
 */
class MediaQueryBuilder extends BaseQueryBuilder
{
    /**
     * Select media with given names.
     *
     * @param string ...$names
     *
     * @return static
     */
    public function name(string ...$names): static
    {
        $this->whereIn('name', $names);

        return $this;
    }

    /**
     * Select media with given disks.
     *
     * @param string ...$disks
     *
     * @return static
     */
    public function disk(string ...$disks): static
    {
        $this->whereIn('disk', $disks);

        return $this;
    }

    /**
     * Select media from given folders.
     *
     * @param string ...$folders
     *
     * @return static
     */
    public function folder(string ...$folders): static
    {
        $this->whereIn('folder', $folders);

        return $this;
    }
}
