<?php

namespace App\Http\Resources\Editor;

use App\Models\MenuVersion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorVersionResource.
 *
 * @mixin MenuVersion
 */
class EditorVersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'status' => $this->status,
            'goes_live_at' => $this->local($this->goes_live_at),
            'timezone' => $this->timezone(),
            'created_by' => $this->creator ? ['id' => $this->creator->id, 'name' => $this->creator->name] : null,
            'created_at' => $this->local($this->created_at),
            'applied_at' => $this->local($this->applied_at),
            'failed_at' => $this->local($this->failed_at),
            'failure_reason' => $this->failure_reason,
            'changes_count' => $this->countChanges(),
            'items_count' => $this->countItems(),
            'changes' => EditorVersionChangeResource::collection($this->itemChanges),
        ];
    }

    /**
     * Time zone of the version's restaurant.
     *
     * @return string
     */
    protected function timezone(): string
    {
        return $this->restaurant->timezone ?: config('app.timezone');
    }

    /**
     * Date and time in the restaurant's time zone (with its offset).
     *
     * @param Carbon|null $value
     *
     * @return string|null
     */
    protected function local(?Carbon $value): ?string
    {
        return $value?->clone()->setTimezone($this->timezone())->toIso8601String();
    }

    /**
     * @OA\Schema(
     *   schema="EditorVersion",
     *   description="Scheduled version: changes, which go live together. Dates are in the restaurant's time zone.",
     *   required={"id", "restaurant_id", "name", "status", "goes_live_at", "timezone", "created_by", "created_at",
     *     "applied_at", "failed_at", "failure_reason", "changes_count", "items_count", "changes"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="name", type="string", nullable=true, example="Winter menu",
     *     description="None for a change scheduled on its own."),
     *   @OA\Property(property="status", type="string", example="scheduled",
     *     enum={"draft", "scheduled", "inactive", "applied", "failed"}),
     *   @OA\Property(property="goes_live_at", type="string", format="date-time", nullable=true,
     *     example="2026-11-01T00:00:00+02:00"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="created_by", type="object", nullable=true, required={"id", "name"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Anna")),
     *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="applied_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="failed_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="failure_reason", type="string", nullable=true,
     *     example="A changed dish doesn't exist anymore."),
     *   @OA\Property(property="changes_count", type="integer", example=14,
     *     description="Changed fields, a new item counts as one."),
     *   @OA\Property(property="items_count", type="integer", example=8),
     *   @OA\Property(property="changes", type="array", @OA\Items(ref="#/components/schemas/EditorVersionChange")),
     * ),
     */
}
