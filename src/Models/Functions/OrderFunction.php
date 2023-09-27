<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models\Functions;

use Advancelearn\ManagePaymentAndOrders\Enums\AuditTypes;
use Advancelearn\ManagePaymentAndOrders\Models\Payment;
use Advancelearn\ManagePaymentAndOrders\Transformer\OrderResource;
use App\Models\Shipping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderFunction
{

    const descAuditStore = "Initial order registration";
    const descDestroy = 'It is not possible to delete the order, only the status of the order was changed to canceled.';
    const orderNotRegistered = "The number of order registration requests is more than the stock of the product in the warehouse and your order was not registered";

    /**
     * Retrieve a paginated list of orders.
     *
     * @param int $paginateCount
     * @return LengthAwarePaginator
     */
    public function getOrders(int $paginateCount = 6)
    {
        return $this->queryOrders()
            ->latest()
            ->paginate($paginateCount);
    }


    public function singleOrder($orderId): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|Builder|array|null
    {
        return $this->queryOrders()
            ->findOrfail($orderId);
    }

    /**
     * Retrieve a paginated list of orders for the logged-in user.
     *
     * @param int $paginateCount
     * @return LengthAwarePaginator
     */
    public function ordersOfTheLoggedInUser(int $paginateCount = 6): LengthAwarePaginator
    {
        return $this->queryOrders()
            ->whereHas('address', fn ($query) => $query->where('user_id', Auth::id()))
            ->latest()
            ->paginate($paginateCount);
    }


    /**
     * @param $orderId
     * @return mixed
     */
    public function SingleOrderOfTheLoggedInUser($orderId): mixed
    {
        return $this->queryOrders()
            ->whereId($orderId)
            ->whereHas('address', fn ($query) => $query->where('user_id', Auth::id()))
            ->first();
    }

    /**
     * Common query to fetch orders with relationships.
     *
     * @return Builder
     */
    private function queryOrders(): Builder
    {
        return app('order')
            ->with(['address', 'audits', 'items']);
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
    public function store(int $shippingId = null , int $addressId, string $description, array $items, string $couponCode = null): mixed
    {

        return DB::transaction(function () use ($shippingId, $addressId, $description, $items, $couponCode) {

            $shipping = ($shippingId != null) ? app('shipping')::findOrFail($shippingId) : null;
            $address = app('address')::findOrFail($addressId);
            $items = $this->filterItemsByInventory($items);
            if (count($items) > 0) {
                $order = $this->createOrder($description, $address, $shipping);
                $orderItems = $this->prepareOrderItems($items);

                $this->syncOrderItems($order, $orderItems);

                $amount = $this->getOrderAmount($order);

                $this->updateOrderPrices($order, $amount);

                $this->attachAudit($order);

                return new OrderResource($order);
            }
            return response()->json(['errors' => self::orderNotRegistered], 422);
        });
    }


    /**
     * Generate a unique order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        return dechex(time()) . '-' . dechex(3);
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
            "adm_shippings_id" => ($shipping != null) ? optional($shipping)->id : $shipping,
            "shipping_price" => ($shipping != null) ?optional($shipping)->price : $shipping,
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
    public function update(int $shippingId, int $addressId, string $description, string $shippingDate, array $items, int $auditId, int $orderId, string $couponCode = null)
    {
        return DB::transaction(function () use ($shippingId, $addressId, $description, $shippingDate, $items, $auditId, $orderId, $couponCode) {
            try {
                $order = $this->findOrderById($orderId);
                $filteredItems = $this->filterItemsByInventory($items);
                $this->updateOrderDetails($order, $addressId, $description, $shippingId, $shippingDate);
                $this->updateOrderItems($order, $filteredItems);
                $this->updateOrderAudits($order, $auditId);

                return new OrderResource($order);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()]);
            }
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
        $order->adm_addresses_id = $addressId ?? $order->adm_addresses_id;
        if ($order->address->adm_city_id) {
            $shipping = app('shipping')::find($shippingId);
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
     * @param array $items
     * @return array
     */
    private function filterItemsByInventory(array $items): array
    {
        $filteredItems = array_filter($items, function ($item) {
            $inventory = app('inventory')::find($item['inventory_id']);
            if ($inventory && $inventory->count >= $item['quantity']) {
                return true;
            }
            return false;
        });

        return array_values($filteredItems);
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
        }
        if ($order->address->adm_city_id) {
            $orderPrice += $order->shipping_price;
        }
        return $orderPrice;
    }

    public function getAuditForUpdate($auditId): array
    {
        return match ($auditId) {
            AuditTypes::ORDER_CONFIRMATION => [AuditTypes::ORDER_CONFIRMATION => ['description' => 'order confirmation by admin']],
            AuditTypes::INVENTORY_CONFIRMATION => [AuditTypes::INVENTORY_CONFIRMATION => ['description' => 'order inventories confirmation']],
            AuditTypes::READY_TO_SHIPPING => [AuditTypes::READY_TO_SHIPPING => ['description' => 'Ready to ship']],
            AuditTypes::SHIPPED => [AuditTypes::SHIPPED => ['description' => 'order Sent']],
            AuditTypes::CANCELLED_BY_ADMIN => [AuditTypes::CANCELLED_BY_ADMIN => ['description' => 'order canceled by admin']],
            AuditTypes::CANCELLED_BY_USER => [AuditTypes::CANCELLED_BY_USER => ['description' => 'order canceled by user']],
            AuditTypes::DELIVERED => [AuditTypes::DELIVERED => ['description' => 'order DELIVERED']],
            AuditTypes::EDIT => [AuditTypes::EDIT => ['description' => 'order is edited']],
            AuditTypes::PAID => [AuditTypes::PAID => ['description' => 'order is paid']],
            AuditTypes::REMOVE_BY_SYSTEM => [AuditTypes::REMOVE_BY_SYSTEM => ['description' => 'order REMOVE BY SYSTEM']],
        };

    }

}
