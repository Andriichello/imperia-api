<?php

namespace App\Http\Controllers\Other;

use App\Helpers\Objects\Signature;
use App\Helpers\SignatureHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\GenerateUrlRequest;
use App\Http\Requests\Invoice\ShowInvoiceRequest;
use App\Http\Responses\ApiResponse;
use App\Invoices\InvoiceFactory;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

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

    /**
     * Generate an access url for banquet's invoice endpoint.
     *
     * @param GenerateUrlRequest $request
     *
     * @return ApiResponse
     */
    public function generateUrl(GenerateUrlRequest $request): ApiResponse
    {
        $id = $request->id();

        switch ($request->get('endpoint')) {
            case 'view':
                $path = "api/orders/$id/invoice";
                break;
            case 'pdf':
                $path = "api/orders/$id/invoice/pdf";
                break;
            case 'viewThroughBanquet':
                $path = "api/banquets/$id/invoice";
                break;
            case 'pdfThroughBanquet':
                $path = "api/banquets/$id/invoice/pdf";
                break;
        }

        $signature = (new Signature())
            ->setUserId(request()->user()->id)
            ->setExpiration(now()->addWeek())
            ->setPath($path);

        $signature = (new SignatureHelper())
            ->encrypt($signature);

        $query = http_build_query(compact('signature'));

        $url = Str::of($request->fullUrl())
            ->before('/api')
            ->finish('/' . $path)
            ->finish('?' . $query)
            ->value();

        return ApiResponse::make(compact('url'));
    }

    /**
     * @OA\Post(
     *   path="/api/orders/{id}/invoice/url",
     *   summary="Generate url for accessing order's invoice.",
     *   operationId="orderInvoiceUrl",
     *   security={{"bearerAuth": {}}},
     *   tags={"invoices"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the order."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Generate url for accessing order's invoice response.",
     *     @OA\JsonContent(ref ="#/components/schemas/OrderInvoiceUrlRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Generate url for accessing order's invoice response.",
     *     @OA\JsonContent(ref ="#/components/schemas/OrderInvoiceUrlResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/banquets/{id}/invoice/url",
     *   summary="Generate url for accessing banquet's invoice.",
     *   operationId="banquetInvoiceUrl",
     *   security={{"bearerAuth": {}}},
     *   tags={"invoices"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the banquet."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Generate url for accessing banquet's invoice response.",
     *     @OA\JsonContent(ref ="#/components/schemas/BanquetInvoiceUrlRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Generate url for accessing banquet's invoice response.",
     *     @OA\JsonContent(ref ="#/components/schemas/BanquetInvoiceUrlResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="OrderInvoiceUrlResponse",
     *   description="Generate url for accessing order's invoice response.",
     *   required = {"url", "message"},
     *   @OA\Property(property="url", type="string",
     *     example="http://host/path?signature=long-string"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="BanquetInvoiceUrlResponse",
     *   description="Generate url for accessing banquet's invoice response.",
     *   required = {"url", "message"},
     *   @OA\Property(property="url", type="string",
     *     example="http://host/path?signature=long-string"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
