<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Menu\AttachCategoryRequest;
use App\Http\Requests\Menu\AttachProductRequest;
use App\Http\Requests\Menu\DetachCategoryRequest;
use App\Http\Requests\Menu\DetachProductRequest;
use App\Http\Requests\Menu\IndexMenuRequest;
use App\Http\Requests\Menu\ShowMenuRequest;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Menu\MenuResource;
use App\Http\Responses\ApiResponse;
use App\Models\Menu;
use App\Policies\MenuPolicy;
use App\Repositories\MenuRepository;

/**
 * Class MenuController.
 *
 * @property MenuRepository $repository
 */
class MenuController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = MenuResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = MenuCollection::class;

    /**
     * MenuController constructor.
     *
     * @param MenuRepository $repository
     * @param MenuPolicy $policy
     */
    public function __construct(MenuRepository $repository, MenuPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexMenuRequest::class;
        $this->actions['show'] = ShowMenuRequest::class;
    }

    /**
     * Attaches category to menu.
     *
     * @param AttachCategoryRequest $request
     *
     * @return ApiResponse
     */
    public function attachCategory(AttachCategoryRequest $request): ApiResponse
    {
        $menuId = $request->get('menu_id');
        $categoryId = $request->get('category_id');

        /** @var Menu $menu */
        $menu = Menu::query()
            ->findOrFail($menuId);

        $this->repository->attachCategory($menu, $categoryId);

        return ApiResponse::make();
    }

    /**
     * Detaches category from menu.
     *
     * @param DetachCategoryRequest $request
     *
     * @return ApiResponse
     */
    public function detachCategory(DetachCategoryRequest $request): ApiResponse
    {
        $menuId = $request->get('menu_id');
        $categoryId = $request->get('category_id');

        /** @var Menu $menu */
        $menu = Menu::query()
            ->findOrFail($menuId);

        $this->repository->detachCategory($menu, $categoryId);

        return ApiResponse::make();
    }

    /**
     * Attaches product to menu.
     *
     * @param AttachProductRequest $request
     *
     * @return ApiResponse
     */
    public function attachProduct(AttachProductRequest $request): ApiResponse
    {
        $menuId = $request->get('menu_id');
        $productId = $request->get('product_id');

        /** @var Menu $menu */
        $menu = Menu::query()
            ->findOrFail($menuId);

        $this->repository->attachProduct($menu, $productId);

        return ApiResponse::make();
    }

    /**
     * Detaches product from menu.
     *
     * @param DetachProductRequest $request
     *
     * @return ApiResponse
     */
    public function detachProduct(DetachProductRequest $request): ApiResponse
    {
        $menuId = $request->get('menu_id');
        $productId = $request->get('product_id');

        /** @var Menu $menu */
        $menu = Menu::query()
            ->findOrFail($menuId);

        $this->repository->detachProduct($menu, $productId);

        return ApiResponse::make();
    }

    /**
     * @OA\Get(
     *   path="/api/menus",
     *   summary="Index menus.",
     *   operationId="indexMenus",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/MenuIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="-popularity", @OA\Schema(type="string"),
            description="Available sorts: `popularity` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *   @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits menus to those
     * that are attached at least to one of those restaurants"),
     *   @OA\Parameter(name="archived", in="query", @OA\Schema(ref ="#/components/schemas/ArchivedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index menus response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/menus/{id}",
     *   summary="Show menus by id.",
     *   operationId="showMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menus."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/MenuIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show menus response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexMenuResponse",
     *   description="Index menus response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Menu")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowMenuResponse",
     *   description="Show menu response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Menu"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="MenuIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `products`",
     *   type="string", example="products"
     * )
     */

    /**
     * @OA\Post(
     *   path="/api/menus/attach-category",
     *   summary="Attach category to menu.",
     *   operationId="attachCategoryToMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Attach category to menu request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/AttachCategoryToMenuRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Attach category to menu response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/AttachCategoryToMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/menus/detach-category",
     *   summary="Detach category from menu.",
     *   operationId="detachCategoryFromMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Detach category from menu request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DetachCategoryFromMenuRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Detach category from menu response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DetachCategoryFromMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Post(
     *   path="/api/menus/attach-product",
     *   summary="Attach product to menu.",
     *   operationId="attachProductToMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Attach product to menu request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/AttachProductToMenuRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Attach product to menu response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/AttachProductToMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/menus/detach-product",
     *   summary="Detach product from menu.",
     *   operationId="detachProductFromMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Detach product from menu request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DetachProductFromMenuRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Detach product from menu response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DetachProductFromMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="AttachCategoryToMenuResponse",
     *   description="Attach category to menu response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DetachCategoryFromMenuResponse",
     *   description="Detach category from menu response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
      * @OA\Schema(
     *   schema="AttachProductToMenuResponse",
     *   description="Attach category to menu response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DetachProductFromMenuResponse",
     *   description="Detach product from menu response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
