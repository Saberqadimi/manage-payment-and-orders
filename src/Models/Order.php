<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $table = "adm_orders";
    protected $guarded = [];

    public function index()
    {
        echo "return all data orders from database";
    }

    /**
     * @return BelongsTo
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(app('address') , 'adm_addresses_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function shipping(): BelongsTo
    {
        return $this->belongsTo(app('shipping') , 'adm_shippings_id');
    }

    /**
     * @return BelongsToMany
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(app('inventory'), 'adm_order_items')->withPivot('quantity', 'price')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function audits(): BelongsToMany
    {
        return $this->belongsToMany(app('audit'), 'adm_order_audits')->withPivot('description')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(app('payment'));
    }

}
