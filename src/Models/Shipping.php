<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $table="adm_shippings";
    protected $fillable = [
        'title',
        'price',
        'description'
    ];
}
