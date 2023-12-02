<?php

namespace App\Http\Requests\Metrics;

use App\Models\Menu;
use App\Models\Restaurant;

/**
 * Class FullReportRequest.
 */
class FullReportRequest extends WithinTimeFrameRequest
{
    /**
     * Restaurant to generate the report for.
     *
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * Menus to use in report.
     *
     * @var array|Menu[]
     */
    protected array $menus;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'restaurant_id' => [
                    'required',
                    'integer',
                    'exists:restaurants,id',
                ],
            ]
        );
    }

    /**
     * Get the restaurant to generate the report for.
     *
     * @return Restaurant
     */
    public function restaurant(): Restaurant
    {
        if (isset($this->restaurant)) {
            return $this->restaurant;
        }

        /** @var Restaurant $restaurant */
        $restaurant = Restaurant::query()
            ->findOrFail($this->get('restaurant_id'));

        return $this->restaurant = $restaurant;
    }

    /**
     * Get an array of menus to use in report.
     *
     * @return array
     */
    public function menus(): array
    {
        if (isset($this->menus)) {
            return $this->menus;
        }

        /** @var Menu[] $menus */
        $menus = $this->restaurant()
            ->menus()
            ->get()
            ->all();

        return $this->menus = $menus;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return !$user->restaurant_id
            || $user->restaurant_id === $this->integer('restaurant_id');
    }
}
