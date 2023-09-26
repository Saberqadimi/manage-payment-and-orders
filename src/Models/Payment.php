<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table="adm_payments";

    protected $fillable = [
        'order_id',
        'description',
        'transaction',
        'transaction_id',
        'driver',
        'amount'
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(app('order'));
    }

}
