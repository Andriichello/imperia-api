<?php

namespace Andriichello\Media;

use App\Models\Interfaces\MediableInterface;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Symfony\Component\Console\Output\ConsoleOutput;

class MediaField extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'media-field';

    /**
     * MediaField constructor.
     *
     * @param $name
     * @param $attribute
     * @param callable|null $resolveCallback
     */
    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->fillCallback = function (NovaRequest $request, MediableInterface $model) {
            $ids = Str::of($request->get($this->attribute, ''))
                ->explode(',')
                ->map(fn($id) => is_numeric($id) ? intval($id) : null)
                ->filter();

            $model->setMedia(...$ids->all());
        };
    }
}
