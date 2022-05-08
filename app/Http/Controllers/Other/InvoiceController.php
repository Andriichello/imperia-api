<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\ShowInvoiceRequest;
use App\Invoices\InvoiceFactory;
use App\Models\Banquet;
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
     * Generate banquet's order invoice html.
     *
     * @param ShowInvoiceRequest $request
     *
     * @return View
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function view(ShowInvoiceRequest $request): View
    {
        /** @var Banquet $banquet */
        $banquet = Banquet::query()
            ->findOrFail($request->id());

        if (!$banquet->order) {
            throw new Exception('Banquet has no order.');
        }

        return InvoiceFactory::fromOrder($banquet->order)->render()->toHtml();
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
        /** @var Banquet $banquet */
        $banquet = Banquet::query()
            ->findOrFail($request->id());

        if (!$banquet->order) {
            throw new Exception('Banquet has no order.');
        }

        return InvoiceFactory::fromOrder($banquet->order)->stream();
    }
}
