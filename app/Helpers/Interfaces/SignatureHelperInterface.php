<?php

namespace App\Helpers\Interfaces;

use App\Enums\BanquetState;
use App\Models\Banquet;
use App\Models\User;
use Carbon\Carbon;

/**
 * Interface SignatureHelperInterface.
 */
interface SignatureHelperInterface
{
    /**
     * Make a signature for given user.
     *
     * @param User $user
     * @param Carbon $expiration
     *
     * @return string
     */
    public function make(User $user, Carbon $expiration): string;

    /**
     * Extract user id from given signature.
     *
     * @param string $signature
     *
     * @return mixed
     */
    public function userId(string $signature): mixed;

    /**
     * Check if given signature is valid.
     *
     * @param string $signature
     *
     * @return bool
     */
    public function verify(string $signature): bool;
}
