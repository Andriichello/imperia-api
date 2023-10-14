<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Alteration;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Trait AlterableTrait.
 *
 * @mixin BaseModel
 *
 * @property Alteration[]|Collection $alterations
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
        // @phpstan-ignore-next-line
        return $this->alterations()
            ->where('perform_at', '<=', now())
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
