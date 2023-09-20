<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table="adm_addresses";

//    public function addressType()
//    {
//        return $this->belongsTo(AddressType::class);
//    }

    protected $fillable = [
        'address',
        'description',
        'receiver',
        'postal_code',
        'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
