<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Transformer;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'count' => $this->count,
            'product_id' => $this->inventories_id,
            'product' => $this->inventories,
            'created_at' => $this->created_at,
            'price' => $this->price,
            'pivot' => $this->pivot,
            'discounted_price' => $this->discounted_price,
            'description' => json_decode($this->description, JSON_UNESCAPED_UNICODE),
        ];
    }
}
