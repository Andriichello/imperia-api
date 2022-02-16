<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

/**
 * Class BaseRequest.
 */
class BaseRequest extends FormRequest
{
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
