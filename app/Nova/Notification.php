<?php

namespace App\Nova;

use App\Nova\Options\NotificationChannelOptions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

/**
 * Class Notification.
 *
 * @mixin \App\Models\Notification
 */
class Notification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Notification::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'subject';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'channel', 'data',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Subject')
                ->required(),

            Text::make('Body')
                ->nullable(),

            Code::make('Payload')
                ->resolveUsing(fn() => json_encode($this->payload, JSON_PRETTY_PRINT))
                ->autoHeight()
                ->rules(['nullable', 'json'])
                ->json()
                ->showOnIndex(),

            Select::make('Channel')
                ->displayUsingLabels()
                ->rules('required')
                ->options(NotificationChannelOptions::all()),

            BelongsTo::make('Sender', 'sender', User::class)
                ->nullable(),

            BelongsTo::make('Receiver', 'receiver', User::class),

            DateTime::make('Send At')
                ->sortable()
                ->rules(['required', 'date', 'after_or_equal:today']),

            DateTime::make('Sent At')
                ->sortable()
                ->rules(['nullable', 'date', 'after_or_equal:send_at']),

            DateTime::make('Seen At')
                ->sortable()
                ->rules(['nullable', 'date', 'after_or_equal:sent_at']),
        ];
    }

    /**
     * Get columns filter fields.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function columnsFilterFields(Request $request): array
    {
        return [
            'id' => true,
            'subject' => true,
            'body' => true,
            'channel' => true,
            'sender' => false,
            'receiver' => false,
            'send_at' => true,
            'sent_at' => true,
            'seen_at' => false,
        ];
    }
}
