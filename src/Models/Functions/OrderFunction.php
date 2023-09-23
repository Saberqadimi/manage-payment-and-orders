<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models\Functions;

use Advancelearn\ManagePaymentAndOrders\Enums\AuditTypes;
use Advancelearn\ManagePaymentAndOrders\Models\Payment;
use Advancelearn\ManagePaymentAndOrders\Transformer\OrderResource;
use App\Models\Shipping;
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
            $shipping = app('shipping')::findOrFail($shippingId);
            $address = app('address')::findOrFail($addressId);
            $orderNumber = dechex(time()) . '-' . dechex(Auth::user()->id);

            $order = app('order')::forceCreate([
                "order_number" => $orderNumber,
                "description" => $description,
                "adm_addresses_id" => $address->id,
                "adm_shippings_id" => optional($shipping)->id,
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

            $order->audits()->attach([AuditTypes::ORDER_REGISTRATION => ['description' => 'Initial order registration']]);

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
    public function update(int $shippingId, int $addressId, string $description, string $shippingDate, array $items, int $auditId, int $orderId, string $couponCode = null)
    {
        return DB::transaction(function () use ($shippingId, $addressId, $description, $shippingDate, $items, $auditId, $couponCode, $orderId) {
            $order = app('order')::findOrFail($orderId);
            $order->description = $description;
            $order->adm_addresses_id = $addressId;
            if ($order->address->city_id) {
                $shipping = app('shipping')::find($shippingId);
                $order->adm_shippings_id = $shipping->id;
                $order->shipping_price = $shipping->price;
            }
            $order->shipping_date = $shippingDate;
            $orderItems = $items;
            foreach ($orderItems as &$item) {
                $item['price'] = isset($item['price']) && $item['price'] ?
                    $item['price'] :
                    app('inventory')::find($item['inventory_id'])->price;
            }

            $order->items()->sync($orderItems);
            $amount = $this->getOrderAmount($order);
            $order->order_price = $amount;
            $order->payment_price = $amount;
            $order->audits->map(function ($item) use($auditId , $order) {
                if ($item->audit_id != $auditId) {
                    $order->audits()->attach($this->getAuditForUpdate($auditId));
                }
            });

            $order->save();

            return new OrderResource($order);

        });
    }


    /**
     * @param $order
     * @return JsonResponseAlias
     */
    public
    function destroyByAdmin($order): JsonResponseAlias
    {
        $order = app('order')::findOrFail($order);
        $order->audits()->attach($this->getAuditForUpdate(6));
        return response()->json(['errors' => 'It is not possible to delete the order, only the status of the order was changed to canceled.'], 422);
    }

    /**
     * @param $order
     * @return JsonResponseAlias
     */
    public
    function destroyByUser($order): JsonResponseAlias
    {
        $order = app('order')::findOrFail($order);
        $order->audits()->attach($this->getAuditForUpdate(7));
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
            if ($order->shipping_price > 0) {
                $orderPrice += $order->shipping_price;
            }
        }
        return $orderPrice;
    }

    public function getAuditForUpdate($auditId): array
    {
        return match ($auditId){
            AuditTypes::ORDER_CONFIRMATION =>  [AuditTypes::ORDER_CONFIRMATION => ['description' => 'order confirmation by admin']],
            AuditTypes::INVENTORY_CONFIRMATION =>  [AuditTypes::INVENTORY_CONFIRMATION => ['description' => 'order inventories confirmation']],
            AuditTypes::READY_TO_SHIPPING =>  [AuditTypes::READY_TO_SHIPPING => ['description' => 'Ready to ship']],
            AuditTypes::SHIPPED =>  [AuditTypes::SHIPPED => ['description' => 'order Sent']],
            AuditTypes::CANCELLED_BY_ADMIN =>  [AuditTypes::CANCELLED_BY_ADMIN => ['description' => 'order canceled by admin']],
            AuditTypes::CANCELLED_BY_USER =>  [AuditTypes::CANCELLED_BY_USER => ['description' => 'order canceled by user']],
            AuditTypes::DELIVERED =>  [AuditTypes::DELIVERED => ['description' => 'order DELIVERED']],
            AuditTypes::EDIT =>  [AuditTypes::EDIT => ['description' => 'order is edited']],
            AuditTypes::PAID =>  [AuditTypes::PAID => ['description' => 'order is paid']],
            AuditTypes::REMOVE_BY_SYSTEM =>  [AuditTypes::REMOVE_BY_SYSTEM => ['description' => 'order REMOVE BY SYSTEM']],
        };

    }

}
