<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models\Functions;

use Advancelearn\ManagePaymentAndOrders\Enums\AuditTypes;
use Advancelearn\ManagePaymentAndOrders\Transformer\OrderResource;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderFunction
{

    public function getOrders(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return OrderResource::collection(app('order')::latest()->paginate(12));
    }

    public function store(int $shippingId, int $addressId, string $description, array $items, string $couponCode = null)
    {
        return DB::transaction(function () use ($shippingId, $addressId, $description, $items, $couponCode) {
            $shipping = app('shipping')::find($shippingId);
            $address = app('address')::findOrFail($addressId);

            $orderNumber = dechex(time()) . '-' . dechex(Auth::user()->id);

            $order = app('order')::forceCreate([
                "order_number" => $orderNumber,
                "description" => $description,
                "address_id" => $address->id,
                "shipping_id" => optional($shipping)->id,
                "shipping_price" => optional($shipping)->price,
                "tax" => 0
            ]);

            $orderItems = collect($items)->map(function ($item) {
                $inventory = app('inventory')::find($item['inventory_id']);
                $item['price'] = $inventory->price ?? 0;
                return $item;
            })->toArray();

            $order->items()->sync($orderItems);

            $amount = $this->getOrderAmount($order);
            $order->update([
                'order_price' => $amount,
                'payment_price' => $amount,
            ]);

            $order->audits()->attach([app('auditTypes')::ORDER_REGISTRATION => ['description' => 'Initial order registration']]);

            return new OrderResource($order);
        });
    }

    public function show($order)
    {
        $order = app('order')::findOrFail($order);
        if ($order->address->user_id === Auth::user()->id) {
            return new OrderResource($order);
        }
    }

    /**
     * @param int $shippingId
     * @param int $addressId
     * @param string $description
     * @param string $shippingDate
     * @param array $items
     * @param array $audits
     * @param int $orderId
     * @param string|null $couponCode
     * @return mixed
     */
    public function update(int $shippingId, int $addressId, string $description, string $shippingDate, array $items, array $audits, int $orderId , string $couponCode = null)
    {
        return DB::transaction(function () use ($shippingId, $addressId, $description, $shippingDate, $items, $audits, $couponCode, $orderId) {
            $order = app('order')::findOrFail($orderId);

            $order->update([
                'description' => $description,
                'address_id' => $addressId,
                'shipping_id' => $order->address->city_id ? app('shipping')::find($shippingId)->id : null,
                'shipping_price' => $order->address->city_id ? app('shipping')::find($shippingId)->price : null,
                'shipping_date' => $shippingDate,
            ]);

            $orderItems = collect($items)->map(function ($item) {
                $inventory = app('inventory')::find($item['inventory_id']);
                $item['price'] = $item['price'] ?? ($inventory->price ?? 0);
                return $item;
            })->toArray();

            $order->items()->sync($orderItems);

            $amount = $this->getOrderAmount($order);
            $order->update([
                'order_price' => $amount,
                'payment_price' => $amount,
            ]);

            $newAudits = collect($audits)->filter(function ($item) {
                return isset($item['id']) && $item['id'] == 0;
            });

            $order->audits()->attach($newAudits->pluck('id')->toArray());

            return new OrderResource($order);
        });
    }


    /**
     * @param $order
     * @return JsonResponseAlias
     */
    public
    function destroy($order): JsonResponseAlias
    {
        $order = app('order')::findOrFail($order);
        $order->audits()->attach([AuditTypes::CANCELLED_BY_ADMIN => ['description' => 'canceled by admin']]);
        return response()->json(['errors' => 'It is not possible to delete the order, only the status of the order was changed to canceled.'], 422);
    }

    /**
     * @param $order
     * @return float|int
     */
    private function getOrderAmount($order): float|int
    {
        $orderPrice = 0;

        foreach ($order->items as &$item) {
            $orderPrice += $item->pivot->quantity * $item->pivot->price;
        }

        return $orderPrice;
    }

}
