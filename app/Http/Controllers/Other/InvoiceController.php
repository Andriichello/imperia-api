<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\ShowInvoiceRequest;
use App\Invoices\InvoiceFactory;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

/**
 * Class InvoiceController.
 */
class InvoiceController extends Controller
{
    /**
     * Generate order invoice html.
     *
     * @param ShowInvoiceRequest $request
     *
     * @return View
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function view(ShowInvoiceRequest $request): View
    {
        /** @var Order $target */
        $target = $request->targetOrFail(Order::class);

        return InvoiceFactory::fromOrder($target)->render()->toHtml();
    }

    /**
     * Generate banquet's order invoice html.
     *
     * @param ShowInvoiceRequest $request
     *
     * @return View
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function viewThroughBanquet(ShowInvoiceRequest $request): View
    {
        /** @var Banquet $target */
        $target = $request->targetOrFail(Banquet::class);

        if ($target->order) {
            $request->id($target->order_id);
            return $this->view($request);
        }

        throw new Exception('Banquet has no order.');
    }

    /**
     * Generate banquet's order invoice pdf.
     *
     * @param ShowInvoiceRequest $request
     *
     * @return Response
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function pdf(ShowInvoiceRequest $request): Response
    {
        /** @var Order $target */
        $target = $request->targetOrFail(Order::class);

        return InvoiceFactory::fromOrder($target)->stream();
    }

    /**
     * Generate banquet's order invoice pdf.
     *
     * @param ShowInvoiceRequest $request
     *
     * @return Response
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function pdfThroughBanquet(ShowInvoiceRequest $request): Response
    {
        /** @var Banquet $target */
        $target = $request->targetOrFail(Banquet::class);

        if ($target->order) {
            $request->id($target->order_id);
            return $this->pdf($request);
        }

        throw new Exception('Banquet has no order.');
    }
}
