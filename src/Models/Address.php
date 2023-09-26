<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $table = "adm_addresses";

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

    public function city()
    {
        return $this->belongsTo(app('city'));
    }

    public function orders()
    {
        return $this->hasMany(app('order'));
    }
}
