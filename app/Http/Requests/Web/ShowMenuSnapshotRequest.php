<?php

namespace App\Http\Requests\Web;

use App\Helpers\ContentLocale;
use App\Helpers\RestaurantHelper;
use App\Http\Requests\BaseRequest;
use App\Models\Restaurant;
use Illuminate\Validation\Rule;

/**
 * Class ShowMenuSnapshotRequest.
 *
 * The dishes guests see on the pages of the restaurant of the route's `{id}` (its id or slug), in
 * the language. Anyone can make it, and gets the same dishes: what's hidden or archived is never
 * there, whoever asks and however (they're built into files everyone gets).
 */
class ShowMenuSnapshotRequest extends BaseRequest
{
    /**
     * The restaurant the request is about.
     *
     * @var Restaurant|null
     */
    protected ?Restaurant $restaurant = null;

    /**
     * The restaurant the request is about (a restaurant, which doesn't exist, is not found).
     *
     * @return Restaurant
     */
    public function restaurant(): Restaurant
    {
        return $this->restaurant ??= RestaurantHelper::find((string) $this->route('id')) ?? abort(404);
    }

    /**
     * Anyone can make the request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Staff's options of seeing what's archived or deleted don't apply: they're read from the
     * app's request by the models' scopes, so they go from it. The restaurant is found first.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        foreach (['archived', 'deleted', 'filter'] as $option) {
            request()->query->remove($option);
        }

        $this->restaurant();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', Rule::in(ContentLocale::supported())],
            // of the snapshot the page knows: when it's still the current one, it's cached for long
            'hash' => ['sometimes', 'string', 'size:40'],
        ];
    }
}
