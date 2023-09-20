<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $table="adm_audits";
    protected $timestamp = false;

}
