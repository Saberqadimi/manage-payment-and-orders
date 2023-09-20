<?php

namespace Advancelearn\ManagePaymentAndOrders\Services;

interface InventoryInterface
{
    /*
     * "This is a sample model implementation file.
     *  It is required to implement the following method
     *  for handling product details after payment confirmation."
     * */
    public function paymentConfirmation($user_id, $inventory_id, $quantity);
    /** Example
     * You add this method inside the model where you can handle the number of products and warehouse management
     *
     *$inventory = $this->inventories()->find($inventory_id);
     * $inventory->count -= $quantity;
     * $inventory->save();
     */
}
