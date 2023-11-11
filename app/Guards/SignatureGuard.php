<?php

namespace App\Guards;

use App\Helpers\Interfaces\SignatureHelperInterface;
use App\Helpers\Objects\Signature;
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
    protected SignatureHelperInterface $helper;

    /**
     * Create a new authentication guard.
     *
     * @param UserProvider $provider
     * @param Request $request
     * @param SignatureHelperInterface $helper
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        SignatureHelperInterface $helper,
    ) {
        $this->provider = $provider;
        $this->request = $request;
        $this->helper = $helper;
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
        if (!$signature || !$this->helper->verify($signature, $this->request)) {
            return null;
        }

        return $this->user = $this->provider->retrieveById($signature->getUserId());
    }

    /**
     * Get the signature for the current request.
     *
     * @return Signature|null
     */
    public function getSignatureForRequest(): ?Signature
    {
        $encrypted = $this->request->get('signature');

        return $encrypted ? $this->helper->decrypt($encrypted) : null;
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
        $encrypted = data_get($credentials, 'signature');
        if (empty($encrypted)) {
            return false;
        }

        $signature = $this->helper->decrypt($encrypted);

        return $this->helper->verify($signature, $this->request);
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
