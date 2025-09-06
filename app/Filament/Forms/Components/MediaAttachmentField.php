<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

/**
 * MediaAttachmentField: A reusable Filament v3 form field for attaching media via API.
 */
class MediaAttachmentField extends Field
{
    protected string $view = 'filament.forms.components.media-attachment-field';

    protected ?string $modelType = null;

    /** @var array<string> */
    protected array $acceptedFileTypes = [];

    protected int $maxFiles = 0; // 0 = unlimited

    // Max size in KB (to align with Filament convention examples)
    protected int $maxSize = 0; // 0 = unlimited

    protected bool $preview = true;

    protected bool $multiple = true;

    protected ?string $disk = null;

    protected function setUp(): void
    {
        parent::setUp();

        // The field itself does not dehydrate any value to the model column.
        $this->dehydrated(false);
    }

    public function modelType(string $type): static
    {
        $this->modelType = $type;
        return $this;
    }

    /**
     * @param array<string> $types
     */
    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;
        return $this;
    }

    public function maxFiles(int $max): static
    {
        $this->maxFiles = $max;
        return $this;
    }

    /**
     * Size in KB (example: 2048 = 2MB)
     *
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */
    public function maxSize(int $kb): static
    {
        $this->maxSize = $kb;
        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function preview(bool $enabled = true): static
    {
        $this->preview = $enabled;
        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function multiple(bool $enabled = true): static
    {
        $this->multiple = $enabled;
        return $this;
    }

    public function disk(?string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'mediaAttachment' => [
                'modelType' => $this->modelType,
                'acceptedFileTypes' => $this->acceptedFileTypes,
                'maxFiles' => $this->maxFiles,
                'maxSize' => $this->maxSize,
                'preview' => $this->preview,
                'multiple' => $this->multiple,
                'disk' => $this->disk,
            ],
        ]);
    }
}
