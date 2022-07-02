<?php

namespace App\Helpers;

use App\Helpers\Interfaces\SignatureHelperInterface;
use App\Helpers\Objects\Signature;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class SignatureHelper.
 */
class SignatureHelper implements SignatureHelperInterface
{
    /**
     * Encrypt signature.
     *
     * @param Signature $signature
     *
     * @return string
     */
    public function encrypt(Signature $signature): string
    {
        return encrypt($signature->getPayload());
    }

    /**
     * Decrypt signature.
     *
     * @param string $signature
     *
     * @return Signature
     */
    public function decrypt(string $signature): Signature
    {
        return new Signature((array)decrypt($signature));
    }

    /**
     * Determine if user exists.
     *
     * @param Signature $signature
     *
     * @return bool
     */
    public function exists(Signature $signature): bool
    {
        if (!$signature->getUserId()) {
            return false;
        }

        return User::query()
            ->whereKey($signature->getUserId())
            ->exists();
    }

    /**
     * Determine if signature expired.
     *
     * @param Signature $signature
     *
     * @return bool
     */
    public function expired(Signature $signature): bool
    {
        if (!$signature->getExpiration()) {
            return false;
        }

        return $signature->getExpiration()->isPast();
    }

    /**
     * Determine signature enables to perform request.
     *
     * @param Signature $signature
     * @param Request|null $request
     *
     * @return bool
     */
    public function enables(Signature $signature, ?Request $request): bool
    {
        if (!$signature->getPath() || !$request) {
            return true;
        }

        return $signature->getPath() === $request->path();
    }

    /**
     * Check if given signature is valid.
     *
     * @param Signature $signature
     * @param Request|null $request
     *
     * @return bool
     */
    public function verify(Signature $signature, ?Request $request = null): bool
    {
        return !$this->expired($signature)
            && $this->enables($signature, $request)
            && $this->exists($signature);
    }
}
