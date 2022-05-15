<?php

namespace Tests\Guards;

use App\Guards\SignatureGuard;
use App\Helpers\SignatureHelper;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Tests\RegisteringTestCase;

/**
 * Class SignatureGuardTest.
 */
class SignatureGuardTest extends RegisteringTestCase
{
    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = false;

    /**
     * If true, then user should be registered on set up.
     *
     * @var bool
     */
    protected bool $shouldRegister = true;

    /**
     * Test if guard determines user correctly with given signature.
     *
     * @throws Exception
     */
    public function testWithSignature()
    {
        /** @var SignatureHelper $signer */
        $signer = app(SignatureHelper::class);

        $user = $this->user;
        $expiration = Carbon::now()->addHour();

        $signature = $signer->make($user, $expiration);
        $credentials = compact('signature');

        /** @var SignatureGuard $guard */
        $guard = app(SignatureGuard::class);

        $this->assertTrue($guard->validate($credentials));

        $request = new Request($credentials);
        $guard->setRequest($request);
        /** @var User|null $resolved */
        $resolved = $guard->user();

        $this->assertNotEmpty($resolved);
        $this->assertTrue($user->is($resolved));
    }
}
