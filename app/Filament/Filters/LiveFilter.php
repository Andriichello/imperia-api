<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LiveFilter.
 *
 * Shows all, only live or only not live (hidden or archived) records.
 */
class LiveFilter extends TernaryFilter
{
    /**
     * The attribute, which is the opposite of "Live": `is_hidden` (of menus, categories
     * and dishes, which aren't live when archived either) or `archived` (of variants).
     *
     * @var string
     */
    protected string $hiddenBy = 'is_hidden';

    /**
     * Get the default name of the filter.
     *
     * @return string|null
     */
    public static function getDefaultName(): ?string
    {
        return 'live';
    }

    /**
     * Set the attribute, which is the opposite of "Live".
     *
     * @param string $attribute
     *
     * @return static
     */
    public function hiddenBy(string $attribute): static
    {
        $this->hiddenBy = $attribute;

        return $this;
    }

    /**
     * Set up the filter.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Status')
            ->placeholder('All')
            ->trueLabel('Live')
            ->falseLabel('Not live')
            ->queries(
                true: fn (Builder $query) => $query->where(function (Builder $query) {
                    $query->where($query->qualifyColumn($this->hiddenBy), false);

                    if ($this->hiddenBy !== 'archived') {
                        $query->where($query->qualifyColumn('archived'), false);
                    }
                }),
                false: fn (Builder $query) => $query->where(function (Builder $query) {
                    $query->where($query->qualifyColumn($this->hiddenBy), true);

                    if ($this->hiddenBy !== 'archived') {
                        $query->orWhere($query->qualifyColumn('archived'), true);
                    }
                }),
                blank: fn (Builder $query) => $query,
            );
    }
}
