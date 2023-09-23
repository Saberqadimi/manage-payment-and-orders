<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table="adm_inventories";
    protected $fillable = ['inventories_id', 'count', 'price', 'description', 'inventories_type'];

    public function paymentConfirmation($user_id)
    {
        $inventory_id = $this->pivot->inventory_id;
        $quantity =  $this->pivot->quantity;

        return $this->inventories->PaymentConfirmation($user_id, $inventory_id, $quantity);
    }

    public function inventories(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }


    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(app('orderItem'));
    }

}
