<?php

namespace Advancelearn\ManagePaymentAndOrders\Transformer;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{

    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'transaction' => json_decode($this->transaction),
            'reference_id' => $this->reference_id,
            'driver' => $this->driver,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
