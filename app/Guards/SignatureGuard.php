<?php

namespace App\Guards;

use App\Helpers\SignatureHelper;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

/**
 * Class SignatureGuard.
 */
class SignatureGuard extends TokenGuard
{
    /**
     * The signature helper.
     *
     * @var SignatureHelper
     */
    protected SignatureHelper $signer;

    /**
     * Create a new authentication guard.
     *
     * @param UserProvider $provider
     * @param Request $request
     * @param string $inputKey
     * @param string $storageKey
     * @param bool $hash
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        $inputKey = 'api_token',
        $storageKey = 'api_token',
        $hash = false,
    ) {
        parent::__construct($provider, $request, $inputKey, $storageKey, $hash);

        $this->signer = app(SignatureHelper::class);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user()
    {
        if (parent::user()) {
            return $this->user;
        }

        $signature = $this->getSignatureForRequest();
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
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (parent::validate()) {
            return true;
        }

        $signature = data_get($credentials, 'signature', '');
        $id = $this->signer->userId($signature);

        if (!$id || !$this->signer->verify($signature)) {
            return false;
        }

        return $this->provider->retrieveById($id) !== null;
    }
}
