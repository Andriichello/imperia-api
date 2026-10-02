<?php

namespace App\Models\Traits;

use App\Helpers\ContentLocale;
use Spatie\Translatable\HasTranslations;

/**
 * Trait TranslatableTrait.
 *
 * Content in several languages, stored as JSON by language: `{"en": "Soups", "uk": "Супи"}`.
 * Reading an attribute gives it in the current language (or a fallback one), and so does
 * serializing the model, like before content was translatable. The editor works with all
 * languages at once, through `getTranslations()` and `putTranslations()`.
 *
 * @mixin \App\Models\BaseModel
 */
trait TranslatableTrait
{
    use HasTranslations;

    /**
     * Language the model's attributes are read and written in: the one set on the model,
     * the restaurant's default one (in the admin panel), or the app's current one.
     *
     * @return string
     */
    public function getLocale(): string
    {
        if ($this->translationLocale) {
            return $this->translationLocale;
        }

        if (ContentLocale::instance()->usesDefaults()) {
            return $this->getDefaultLocale();
        }

        return config('app.locale');
    }

    /**
     * Fill the model with an array of attributes. Those, which decide its default language
     * (its restaurant or that restaurant's language), go first: the translatable ones
     * may be written in it.
     *
     * @param array $attributes
     *
     * @return static
     */
    public function fill(array $attributes): static
    {
        $first = array_intersect_key($attributes, array_flip(['locale', 'restaurant_id', 'menu_id']));

        return parent::fill($first + $attributes);
    }

    /**
     * Attributes in the current language, like before content was translatable.
     *
     * @return array
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getTranslatableAttributes() as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->getAttributeValue($key);
            }
        }

        return $attributes;
    }

    /**
     * Replace all translations of the attribute (empty ones are left out),
     * or set it to null if there are none.
     *
     * @param string $key
     * @param array|null $translations
     *
     * @return static
     */
    public function putTranslations(string $key, ?array $translations): static
    {
        $translations = array_filter(
            $translations ?? [],
            fn ($value) => $value !== null && trim((string) $value) !== '',
        );

        if (empty($translations)) {
            $this->forgetTranslations($key, true);

            return $this;
        }

        $this->replaceTranslations($key, array_map(fn ($value) => trim((string) $value), $translations));

        return $this;
    }
}
