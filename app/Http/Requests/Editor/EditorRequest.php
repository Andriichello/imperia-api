<?php

namespace App\Http\Requests\Editor;

use App\Helpers\ContentLocale;
use App\Http\Requests\BaseRequest;
use App\Models\BaseModel;
use App\Models\Restaurant;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EditorRequest.
 *
 * Request of the admin editor about a restaurant or something of it (a menu,
 * a category or a dish), which is found by the `{id}` of the route, hidden and
 * archived ones included. Only admins, who can update the restaurant, can make it.
 */
abstract class EditorRequest extends BaseRequest
{
    /**
     * The model the request is about.
     *
     * @var BaseModel|null
     */
    protected ?BaseModel $target = null;

    /**
     * The restaurant of that model.
     *
     * @var Restaurant|null
     */
    protected ?Restaurant $restaurant = null;

    /**
     * Class of the model the request is about.
     *
     * @return class-string<BaseModel>
     */
    abstract protected function targetClass(): string;

    /**
     * Find a model for the editor: hidden and archived ones too, deleted ones never.
     *
     * @param class-string<BaseModel> $class
     * @param int $id
     *
     * @return BaseModel
     */
    public static function findForEditor(string $class, int $id): BaseModel
    {
        $query = $class::query()
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        if (in_array(SoftDeletes::class, class_uses_recursive($class))) {
            $query->whereNull($query->qualifyColumn('deleted_at'));
        }

        /** @var BaseModel $model */
        $model = $query->findOrFail($id);

        return $model;
    }

    /**
     * The model the request is about.
     *
     * @return BaseModel
     */
    public function target(): BaseModel
    {
        return $this->target ??= static::findForEditor($this->targetClass(), (int) $this->route('id'));
    }

    /**
     * The restaurant of the model the request is about.
     *
     * @return Restaurant
     */
    public function restaurant(): Restaurant
    {
        if (!$this->restaurant) {
            $target = $this->target();

            /** @var Restaurant $restaurant */
            $restaurant = $target instanceof Restaurant
                ? $target
                : static::findForEditor(Restaurant::class, (int) $target->getRestaurantId());

            $this->restaurant = $restaurant;
        }

        return $this->restaurant;
    }

    /**
     * Only admins, who can update the restaurant (theirs, or any one for admins without one).
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->restaurant());
    }

    /**
     * Names of fields in validation messages ("The note must not be greater than 120 characters."
     * instead of "The notes.0.text.en must not ...").
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name.*' => 'name',
            'title.*' => 'name',
            'description.*' => 'description',
            'badge.*' => 'badge',
            'address.*' => 'address',
            'establishment' => 'type',
            'brand_primary' => 'primary color',
            'brand_primary_content' => 'text color',
            'notes' => 'notes',
            'notes.*.id' => 'note',
            'notes.*.text' => 'note',
            'notes.*.text.*' => 'note',
            'media' => 'photos',
            'media.*' => 'photo',
            'media.*.id' => 'photo',
            'media.*.is_hidden' => 'photo',
            'file' => 'photo',
            'timezone' => 'time zone',
            'weekdays.*' => 'hours of the day',
            'weekdays.*.*.beg_hour' => 'opening time',
            'weekdays.*.*.beg_minute' => 'opening time',
            'weekdays.*.*.end_hour' => 'closing time',
            'weekdays.*.*.end_minute' => 'closing time',
            'exceptions.*.id' => 'special day',
            'exceptions.*.starts_on' => 'first day',
            'exceptions.*.ends_on' => 'last day',
            'exceptions.*.beg_hour' => 'opening time',
            'exceptions.*.beg_minute' => 'opening time',
            'exceptions.*.end_hour' => 'closing time',
            'exceptions.*.end_minute' => 'closing time',
            'exceptions.*.reason.*' => 'name of the day',
            'closed_until' => 'closing date',
            'closed_reason.*' => 'reason',
            'flags.*' => 'tag',
            'sizes' => 'sizes',
            'sizes.*.id' => 'size',
            'sizes.*.price' => 'price',
            'sizes.*.weight' => 'size',
            'sizes.*.weight_unit' => 'unit',
            'sizes.*.calories' => 'calories',
            'sizes.*.preparation_time' => 'preparation time',
            'sizes.*.is_hidden' => 'size',
            'menu_id' => 'menu',
            'category_id' => 'category',
            'menus.*.id' => 'menu',
            'menus.*.categories.*' => 'category',
            'dishes.*' => 'dish',
        ];
    }

    /**
     * Validated data, with items of lists in the order they were sent: it's built rule by rule,
     * so e.g. items with an `id` would come before ones without it.
     *
     * @param array|int|string|null $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function validated($key = null, $default = null): mixed
    {
        return data_get(static::inSentOrder(parent::validated()), $key, $default);
    }

    /**
     * Sort items of lists (arrays with integer keys only) by their keys, at any depth.
     *
     * @param array $data
     *
     * @return array
     */
    protected static function inSentOrder(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::inSentOrder($value);
            }
        }

        if (!empty($data) && array_filter(array_keys($data), 'is_int') === array_keys($data)) {
            ksort($data);
        }

        return $data;
    }

    /**
     * Rules of a translatable field, which is an object by language: `{"en": "Soups", "uk": "Супи"}`.
     * A required one has to be filled in the restaurant's default language, the others may be empty.
     *
     * @param string $field
     * @param bool $required
     * @param int $max length of each translation
     * @param bool $partial whether the field may be left out (on updates)
     *
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function translationRules(string $field, bool $required, int $max, bool $partial = false): array
    {
        $locales = implode(',', ContentLocale::supported());
        $default = $this->restaurant()->getDefaultLocale();

        return [
            $field => [
                $partial ? 'sometimes' : ($required ? 'required' : 'nullable'),
                ...($required ? [] : ['nullable']),
                "array:$locales",
            ],
            "$field.$default" => [
                $required ? "required_with:$field" : 'nullable',
                ...($required ? [] : ['nullable']),
                'string',
                "max:$max",
            ],
            "$field.*" => ['nullable', 'string', "max:$max"],
        ];
    }
}
