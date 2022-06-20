<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Scopes\ArchivedScope;

/**
 * Trait ArchivableTrait.
 *
 * @mixin BaseModel
 */
trait ArchivableTrait
{
    /**
     * Boot archivable trait.
     *
     * @return void
     */
    public static function bootArchivableTrait(): void
    {
        static::addGlobalScope(new ArchivedScope());
    }
}
