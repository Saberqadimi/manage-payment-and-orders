<?php

namespace Advancelearn\ManagePaymentAndOrders;

interface PaymentConfirmationInterface
{
    public function paymentConfirmation($user_id, $inventory_id, $quantity);
}
