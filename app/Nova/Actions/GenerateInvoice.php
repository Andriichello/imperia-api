<?php

namespace App\Nova\Actions;

use App\Helpers\Objects\Signature;
use App\Helpers\SignatureHelper;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class GenerateInvoice.
 */
class GenerateInvoice extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     *
     * @return array|string[]
     * @throws Exception
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();

        if ($model instanceof Banquet) {
            $id = $model->order_id;
        }

        if ($model instanceof Order) {
            $id = $model->id;
        }

        if (isset($id)) {
            $path = "api/orders/{$model->id}/invoice/pdf";

            $signature = (new Signature())
                ->setUserId(request()->user()->id)
                ->setPath($path);

            $availability = $fields->get('availability', 0);
            switch ($availability) {
                case 1:
                    $signature->setExpiration(Carbon::now()->addDay());
                    break;
                case 2:
                    $signature->setExpiration(Carbon::now()->addWeek());
                    break;
                case 3:
                    $signature->setExpiration(Carbon::now()->addMonth());
                    break;
            }

            $helper = new SignatureHelper();
            $signature = $helper->encrypt($signature);

            $query = http_build_query(compact('signature'));

            $url = url("$path?$query");

            return Action::openInNewTab($url);
        }

        throw new Exception('Selected banquet has no orders, so invoice can\'t be generated.');
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Availability', 'availability')
                ->default(0)
                ->options([
                    0 => __('Forever'),
                    1 => __('Day'),
                    2 => __('Week'),
                    3 => __('Month'),
                ]),
        ];
    }
}
