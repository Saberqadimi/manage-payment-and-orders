<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Transformer;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AddressResource extends JsonResource
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
            'address'=>$this->address,
            'receiver'=>$this->receiver,
            'postal_code'=>$this->postal_code,
            'phone'=>$this->phone,
            'user'=>$this->user()->get()->first(),
            'user_id'=>$this->user_id,
            'city'=>$this->city()->with('province')->get()->first(),
            'city_id'=>$this->city_id,
            'description' => $this->description,
        ];
    }
}
