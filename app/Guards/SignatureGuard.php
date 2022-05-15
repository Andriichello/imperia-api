<?php

namespace App\Guards;

use App\Helpers\Interfaces\SignatureHelperInterface;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

/**
 * Class SignatureGuard.
 */
class SignatureGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var Request
     */
    protected Request $request;

    /**
     * The signature helper.
     *
     * @var SignatureHelperInterface
     */
    protected SignatureHelperInterface $signer;

    /**
     * Create a new authentication guard.
     *
     * @param UserProvider $provider
     * @param Request $request
     * @param SignatureHelperInterface $signer
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        SignatureHelperInterface $signer,
    ) {
        $this->provider = $provider;
        $this->request = $request;
        $this->signer = $signer;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user()
    {
        if (isset($this->user)) {
            return $this->user;
        }

        $signature = $this->getSignatureForRequest();
        if (empty($signature)) {
            return null;
        }

        $id = $this->signer->userId($signature);
        if (!$id || !$this->signer->verify($signature)) {
            return null;
        }

        return $this->user = $this->provider->retrieveById($id);
    }

    /**
     * Get the signature for the current request.
     *
     * @return string|null
     */
    public function getSignatureForRequest(): ?string
    {
        return $this->request->get('signature');
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $signature = data_get($credentials, 'signature');
        if (empty($signature)) {
            return false;
        }

        $id = $this->signer->userId($signature);
        if (!$id || !$this->signer->verify($signature)) {
            return false;
        }

        return $this->provider->retrieveById($id) !== null;
    }

    /**
     * Set request instance.
     *
     * @param Request $request
     *
     * @return static
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
