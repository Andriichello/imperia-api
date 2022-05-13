<?php

namespace App\Models\Interfaces;

/**
 * Interface ArchivableInterface.
 */
interface ArchivableInterface
{
    /**
     * Boot archivable trait.
     *
     * @return void
     */
    public static function bootArchivableTrait(): void;
}
