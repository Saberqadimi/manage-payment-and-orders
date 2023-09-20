<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table="adm_orders";

    public function address()
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class);
    }

    public function items()
    {
        return $this->belongsToMany(Inventory::class, 'adm_order_items')->withPivot('quantity', 'price')->withTimestamps();
    }

    public function audits()
    {
        return $this->belongsToMany(Audit::class, 'adm_order_audits')->withPivot('description')->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
