<?php

namespace App\Queries;

/**
 * Class CacheQueryBuilder.
 */
class CacheQueryBuilder extends BaseQueryBuilder
{
    /**
     * Filter to records, which belong to the given group.
     *
     * @param string $group
     * @param string $boolean
     *
     * @return $this
     */
    public function inGroup(string $group, string $boolean = 'and'): static
    {
        $this->where('key', 'like', "%[%$group%]:<%", boolean: $boolean);

        return $this;
    }

    /**
     * Filter to records, which belong to the given group.
     *
     * @param string $group
     *
     * @return $this
     */
    public function orInGroup(string $group): static
    {
        return $this->inGroup($group, 'or');
    }

    /**
     * Filter to records, which belong to all the given groups.
     *
     * @param string ...$groups
     *
     * @return $this
     */
    public function inAllOfGroups(string ...$groups): static
    {
        $this->whereWrapped(function (CacheQueryBuilder $query) use ($groups) {
            foreach ($groups as $group) {
                $query->inGroup($group);
            }
        });

        return $this;
    }

    /**
     * Filter to records, which belong to any of the given groups.
     *
     * @param string ...$groups
     *
     * @return $this
     */
    public function inAnyOfGroups(string ...$groups): static
    {
        $this->whereWrapped(function (CacheQueryBuilder $query) use ($groups) {
            foreach ($groups as $index => $group) {
                $query->inGroup($group, $index ? 'or' : 'and');
            }
        });

        return $this;
    }

    /**
     * Filter to records, which belong to the given path.
     *
     * @param string $path
     * @param string $boolean
     *
     * @return $this
     */
    public function withPath(string $path, string $boolean = 'and'): static
    {
        $this->where('key', 'like', "%[%]:<$path%>:{", boolean: $boolean);

        return $this;
    }

    /**
     * Filter to records, which belong to the given path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function orWithPath(string $path): static
    {
        return $this->withPath($path, 'or');
    }

    /**
     * Filter to records, which belong to all the given paths.
     *
     * @param string ...$paths
     *
     * @return $this
     */
    public function withAllOfPaths(string ...$paths): static
    {
        $this->whereWrapped(function (CacheQueryBuilder $query) use ($paths) {
            foreach ($paths as $path) {
                $query->withPath($path);
            }
        });

        return $this;
    }

    /**
     * Filter to records, which belong to any of the given groups.
     *
     * @param string ...$paths
     *
     * @return $this
     */
    public function withAnyOfPaths(string ...$paths): static
    {
        $this->whereWrapped(function (CacheQueryBuilder $query) use ($paths) {
            foreach ($paths as $index => $path) {
                $query->withPath($path, $index ? 'or' : 'and');
            }
        });

        return $this;
    }
}
