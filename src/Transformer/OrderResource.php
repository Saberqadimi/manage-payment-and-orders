<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Transformer;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'address' => new AddressResource($this->address),
            'address_id' => $this->adm_addresses_id,
            'user' => $this->address->user ?? null,
            'shipping' => $this->shipping,
            'shipping_id' => $this->adm_shippings_id,
            'shipping_price' => $this->shipping_price,
            'description' => $this->description,
            'tax' => $this->tax,
            'order_price' => $this->order_price,
            'payment_price' => $this->payment_price,
            'shipping_date' => $this->shipping_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => InventoryResource::collection($this->items),
            'audits' => array_reverse($this->audits->toArray()),
        ];
    }
}
