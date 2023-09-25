<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models\Functions;

use Advancelearn\ManagePaymentAndOrders\Enums\AuditTypes;
use Advancelearn\ManagePaymentAndOrders\Models\Payment;
use Advancelearn\ManagePaymentAndOrders\Transformer\OrderResource;
use App\Models\Shipping;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderFunction
{

    const descAuditStore = "Initial order registration";
    const descDestroy = 'It is not possible to delete the order, only the status of the order was changed to canceled.';

    /**
     * @param $paginateCount
     * @return AnonymousResourceCollection
     */
    public function getOrders($paginateCount): AnonymousResourceCollection
    {
        return OrderResource::collection(app('order')::latest()->paginate($paginateCount));
    }

    /**
     * Store a new order.
     *
     * @param int $shippingId
     * @param int $addressId
     * @param string $description
     * @param array $items
     * @param string|null $couponCode
     * @return mixed
     */
    public function store(int $shippingId, int $addressId, string $description, array $items, string $couponCode = null): mixed
    {
        return DB::transaction(function () use ($shippingId, $addressId, $description, $items, $couponCode) {
            $shipping = app('shipping')::findOrFail($shippingId);
            $address = app('address')::findOrFail($addressId);
            $order = $this->createOrder($description, $address, $shipping);

            $orderItems = $this->prepareOrderItems($items);

            $this->syncOrderItems($order, $orderItems);

            $amount = $this->getOrderAmount($order);

            $this->updateOrderPrices($order, $amount);

            $this->attachAudit($order);

            return new OrderResource($order);
        });
    }

    /**
     * Generate a unique order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        return dechex(time()) . '-' . dechex(Auth::user()->id);
    }

    /**
     * Create a new order instance.
     *
     * @param string $description
     * @param mixed $address
     * @param mixed $shipping
     * @return mixed
     */
    private function createOrder(string $description, mixed $address, mixed $shipping): mixed
    {
        return app('order')::forceCreate([
            "order_number" => $this->generateOrderNumber(),
            "description" => $description,
            "adm_addresses_id" => $address->id,
            "adm_shippings_id" => optional($shipping)->id,
            "shipping_price" => optional($shipping)->price,
            "tax" => 0
        ]);
    }

    /**
     * Prepare order items with prices.
     *
     * @param array $items
     * @return array
     */
    private function prepareOrderItems(array $items): array
    {
        return collect($items)->map(function ($item) {
            $inventory = app('inventory')::find($item['inventory_id']);
            $item['price'] = $inventory->price ?? 0;
            return $item;
        })->toArray();
    }

    /**
     * Sync order items with the order.
     *
     * @param mixed $order
     * @param array $orderItems
     */
    private function syncOrderItems(mixed $order, array $orderItems)
    {
        $order->items()->sync($orderItems);
    }

    /**
     * Update order prices.
     *
     * @param mixed $order
     * @param float $amount
     */
    private function updateOrderPrices(mixed $order, float $amount)
    {
        $order->update([
            'order_price' => $amount,
            'payment_price' => $amount,
        ]);
    }

    /**
     * Attach an audit record to the order.
     *
     * @param mixed $order
     */
    private function attachAudit(mixed $order)
    {
        $order->audits()->attach([AuditTypes::ORDER_REGISTRATION => ['description' => self::descAuditStore]]);
    }

    /**
     * @param $order
     * @return OrderResource|void
     */
    public function show($order)
    {
        $order = app('order')::findOrFail($order);
        if ($order->address->user_id === Auth::user()->id) {
            return new OrderResource($order);
        }
    }

    /**
     * Update an order.
     *
     * @param int $shippingId
     * @param int $addressId
     * @param string $description
     * @param string $shippingDate
     * @param array $items
     * @param int $auditId
     * @param int $orderId
     * @param string|null $couponCode
     * @return mixed
     */
    public function update(int $shippingId, int $addressId, string $description, string $shippingDate, array $items, int $auditId, int $orderId, string $couponCode = null): mixed
    {
        return DB::transaction(function () use ($shippingId, $addressId, $description, $shippingDate, $items, $auditId, $orderId, $couponCode) {
            $order = $this->findOrderById($orderId);

            $this->updateOrderDetails($order, $addressId, $description, $shippingId, $shippingDate);
            $this->updateOrderItems($order, $items);
            $this->updateOrderAudits($order, $auditId);

            return new OrderResource($order);
        });
    }

    /**
     * Find an order by its ID.
     *
     * @param int $orderId
     * @return mixed
     */
    private function findOrderById(int $orderId): mixed
    {
        return app('order')::findOrFail($orderId);
    }

    /**
     * Update order details such as description, address, and shipping.
     *
     * @param mixed $order
     * @param int $addressId
     * @param string $description
     * @param int $shippingId
     * @param string $shippingDate
     */
    private function updateOrderDetails($order, int $addressId, string $description, int $shippingId, string $shippingDate)
    {
        $order->description = $description;
        $order->adm_addresses_id = $addressId;

        if ($order->address->city_id && $shipping = app('shipping')::find($shippingId)) {
            $order->adm_shippings_id = $shipping->id;
            $order->shipping_price = $shipping->price;
        }

        $order->shipping_date = $shippingDate;
    }

    /**
     * Update order items.
     *
     * @param mixed $order
     * @param array $items
     */
    private function updateOrderItems($order, array $items)
    {
        $orderItems = $items;

        foreach ($orderItems as &$item) {
            $item['price'] = $item['price'] ?? app('inventory')::find($item['inventory_id'])->price;
        }

        $order->items()->sync($orderItems);
        $amount = $this->getOrderAmount($order);
        $order->order_price = $amount;
        $order->payment_price = $amount;
    }

    /**
     * Update order audits.
     *
     * @param mixed $order
     * @param int $auditId
     */
    private function updateOrderAudits($order, int $auditId)
    {
        $order->audits->map(function ($item) use ($auditId, $order) {
            if ($item->audit_id != $auditId) {
                $order->audits()->attach($this->getAuditForUpdate($auditId));
            }
        });

        $order->save();
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
        return response()->json(['errors' => self::descDestroy], 422);
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
        return response()->json(['errors' => self::descDestroy], 422);
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
