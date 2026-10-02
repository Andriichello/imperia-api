<?php

namespace App\Http\Requests\Editor;

/**
 * Trait MenuRules.
 *
 * Rules of a menu's or a category's texts and visibility.
 *
 * @mixin EditorRequest
 */
trait MenuRules
{
    /**
     * Rules of the title, description and visibility.
     *
     * @param bool $partial whether fields may be left out (on updates)
     *
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function textRules(bool $partial): array
    {
        return [
            ...$this->translationRules('title', true, 255, $partial),
            ...$this->translationRules('description', false, 1000, $partial),
            'is_hidden' => ['sometimes', 'boolean'],
        ];
    }
}
