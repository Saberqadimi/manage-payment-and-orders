<?php

namespace Advancelearn\ManagePaymentAndOrders\Enums;
use ReflectionClass;

enum AuditTypes
{
    public const ORDER_REGISTRATION = 1;
    public const ORDER_CONFIRMATION = 2;
    public const INVENTORY_CONFIRMATION = 3;
    public const READY_TO_SHIPPING = 4;
    public const SHIPPED = 5;
    public const CANCELLED_BY_ADMIN = 6;
    public const CANCELLED_BY_USER = 7;
    public const DELIVERED = 8;
    public const EDIT = 9;
    public const PAID = 10;
    public const REMOVE_BY_SYSTEM = 11;

    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
