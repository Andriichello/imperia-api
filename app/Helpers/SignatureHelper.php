<?php

namespace App\Helpers;

use App\Helpers\Interfaces\SignatureHelperInterface;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class BanquetHelper.
 */
class SignatureHelper implements SignatureHelperInterface
{
    /**
     * Make a signature for given user.
     *
     * @param User $user
     * @param Carbon $expiration
     *
     * @return string
     */
    public function make(User $user, Carbon $expiration): string
    {
        $data = [
            'user_id' => $user->id,
            'expiration' => $expiration->timestamp,
        ];

        return encrypt($data);
    }

    /**
     * Extract user id from given signature.
     *
     * @param string $signature
     *
     * @return mixed
     */
    public function userId(string $signature): mixed
    {
        return data_get(decrypt($signature), 'user_id');
    }

    /**
     * Extract expiration timestamp from given signature.
     *
     * @param string $signature
     *
     * @return Carbon|null
     */
    public function expiration(string $signature): ?Carbon
    {
        $expiration = data_get(decrypt($signature), 'expiration');

        return $expiration ? Carbon::createFromTimestamp($expiration) : null;
    }

    /**
     * Check if given signature is valid.
     *
     * @param string $signature
     *
     * @return bool
     */
    public function verify(string $signature): bool
    {
        $expiration = $this->expiration($signature);

        return $expiration && $expiration->isFuture();
    }
}
