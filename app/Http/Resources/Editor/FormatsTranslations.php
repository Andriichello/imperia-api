<?php

namespace App\Http\Resources\Editor;

use App\Helpers\ContentLocale;
use App\Models\Interfaces\TranslatableInterface;

/**
 * Trait FormatsTranslations.
 */
trait FormatsTranslations
{
    /**
     * Translations of the attribute in every supported language (null where there's none),
     * e.g. `{"en": "Soups", "uk": null}`.
     *
     * @param TranslatableInterface $model
     * @param string $key
     *
     * @return array<string, string|null>
     */
    protected function translations(TranslatableInterface $model, string $key): array
    {
        $translations = $model->getTranslations($key);
        $result = [];

        foreach (ContentLocale::supported() as $locale) {
            $result[$locale] = $translations[$locale] ?? null;
        }

        return $result;
    }
}
