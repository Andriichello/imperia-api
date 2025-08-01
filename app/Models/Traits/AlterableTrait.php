<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Alteration;
use App\Queries\AlterationQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Trait AlterableTrait.
 *
 * @mixin BaseModel
 *
 * @property Alteration[]|Collection $alterations
 * @property Alteration[]|Collection $pendingAlterations
 * @property Alteration[]|Collection $performedAlterations
 */
trait AlterableTrait
{
    /**
     * Alterations related to the model.
     *
     * @return MorphMany
     */
    public function alterations(): MorphMany
    {
        return $this->morphMany(Alteration::class, 'alterable');
    }

    /**
     * Alterations related to the model, but which haven't been performed yet.
     *
     * @return MorphMany
     */
    public function pendingAlterations(): MorphMany
    {
        /** @var MorphMany|AlterationQueryBuilder $morphMany */
        $morphMany = $this->alterations();
        $morphMany->thatHaveNotBeenPerformed();

        return $morphMany;
    }

    /**
     * Alterations related to the model, but which have been already performed.
     *
     * @return MorphMany
     */
    public function performedAlterations(): MorphMany
    {
        /** @var MorphMany|AlterationQueryBuilder $morphMany */
        $morphMany = $this->alterations();
        $morphMany->thatHaveBeenPerformed();

        return $morphMany;
    }

    /**
     * Attach given alterations to the model.
     *
     * @param Alteration ...$alterations
     *
     * @return void
     */
    public function attachAlterations(Alteration ...$alterations): void
    {
        $records = array_map(
            fn(Alteration $alteration) => $alteration->toArray(),
            $alterations
        );

        $this->alterations()->createMany($records);
    }

    /**
     * Detach given alterations from the model.
     *
     * @param Alteration ...$alterations
     *
     * @return void
     */
    public function detachAlterations(Alteration ...$alterations): void
    {
        $ids = array_map(fn(Alteration $alteration) => $alteration->id, $alterations);
        // @phpstan-ignore-next-line
        $this->alterations()
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Determines if model has alterations attached.
     *
     * @return bool
     */
    public function hasAlterations(): bool
    {
        // @phpstan-ignore-next-line
        return $this->alterations()->exists();
    }

    /**
     * Determines if model has pending alterations (which
     * should have already been performed) attached.
     *
     * @return bool
     */
    public function hasPendingAlterations(): bool
    {
        $shouldBePerformed = function (Builder $query) {
            $query->whereNull('perform_at')
                ->orWhere('perform_at', '<=', now());
        };

        // @phpstan-ignore-next-line
        return $this->alterations()
            ->whereNull('performed_at')
            ->where($shouldBePerformed)
            ->exists();
    }

    /**
     * Boot alterable trait.
     *
     * @return void
     */
    public static function bootAlterableTrait(): void
    {
        static::deleted(function ($model) {
            if (!usesTrait($model, SoftDeletes::class)) {
                $model->alterations()->delete();
            }
        });

        if (usesTrait(static::class, SoftDeletes::class)) {
            // @phpstan-ignore-next-line
            static::forceDeleted(function ($model) {
                $model->alterations()->delete();
            });
        }
    }
}
