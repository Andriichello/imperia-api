<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

/**
 * Class BaseRequest.
 */
class BaseRequest extends FormRequest
{
    /**
     * Message, which should be displayed on failed authorization attempt.
     *
     * @var string
     */
    protected string $message = 'You are not authorized to perform this request.';

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws AuthorizationException
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException($this->message);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    /**
     * Create a new request instance from the given Laravel request.
     *
     * @param Request $from
     * @param Request|null $to
     *
     * @return static
     */
    public static function createFrom(Request $from, $to = null): static
    {
        $obj = parent::createFrom($from, $to);
        $obj->setContainer(app());
        $obj->getValidatorInstance();
        $obj->setRedirector(app(Redirector::class));

        return $obj;
    }
}
