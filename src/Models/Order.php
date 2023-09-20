<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = "adm_orders";

    public function index()
    {
        echo "return all data orders from database";
    }

    /**
     * @return BelongsTo
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(app('address'))->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function shipping(): BelongsTo
    {
        return $this->belongsTo(app('shipping'));
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
