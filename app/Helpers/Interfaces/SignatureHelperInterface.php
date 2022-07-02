<?php

namespace App\Helpers\Interfaces;

use App\Helpers\Objects\Signature;
use Illuminate\Http\Request;

/**
 * Interface SignatureHelperInterface.
 */
interface SignatureHelperInterface
{
    /**
     * Encrypt signature.
     *
     * @param Signature $signature
     *
     * @return string
     */
    public function encrypt(Signature $signature): string;

    /**
     * Decrypt signature.
     *
     * @param string $signature
     *
     * @return Signature
     */
    public function decrypt(string $signature): Signature;

    /**
     * Determine if user exists.
     *
     * @param Signature $signature
     *
     * @return bool
     */
    public function exists(Signature $signature): bool;

    /**
     * Determine if signature expired.
     *
     * @param Signature $signature
     *
     * @return bool
     */
    public function expired(Signature $signature): bool;

    /**
     * Determine signature enables to perform request.
     *
     * @param Signature $signature
     * @param Request|null $request
     *
     * @return bool
     */
    public function enables(Signature $signature, ?Request $request): bool;

    /**
     * Check if given signature is valid.
     *
     * @param Signature $signature
     * @param Request|null $request
     *
     * @return bool
     */
    public function verify(Signature $signature, ?Request $request = null): bool;
}
