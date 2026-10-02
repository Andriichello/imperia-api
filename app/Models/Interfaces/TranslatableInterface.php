<?php

namespace App\Models\Interfaces;

/**
 * Interface TranslatableInterface.
 *
 * Models with content written in several languages.
 */
interface TranslatableInterface
{
    /**
     * Default language of the model's content (its restaurant's one).
     *
     * @return string
     */
    public function getDefaultLocale(): string;

    /**
     * Read and write the attributes in the language, instead of the current one.
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale(string $locale): self;

    /**
     * Names of the attributes that have translations.
     *
     * @return string[]
     */
    public function getTranslatableAttributes(): array;

    /**
     * Translations of the attribute, by language (empty ones left out).
     *
     * @param string|null $key
     * @param array|null $allowedLocales
     *
     * @return array
     */
    public function getTranslations(?string $key = null, ?array $allowedLocales = null): array;

    /**
     * Replace all translations of the attribute (empty ones are left out).
     *
     * @param string $key
     * @param array|null $translations
     *
     * @return static
     */
    public function putTranslations(string $key, ?array $translations): static;
}
