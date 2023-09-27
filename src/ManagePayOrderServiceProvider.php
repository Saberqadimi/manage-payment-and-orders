<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders;

use Advancelearn\ManagePaymentAndOrders\Models\Address;
use Advancelearn\ManagePaymentAndOrders\Models\Audit;
use Advancelearn\ManagePaymentAndOrders\Models\City;
use Advancelearn\ManagePaymentAndOrders\Models\Functions\OrderFunction;
use Advancelearn\ManagePaymentAndOrders\Models\Functions\PaymentFunction;
use Advancelearn\ManagePaymentAndOrders\Models\Inventory;
use Advancelearn\ManagePaymentAndOrders\Models\Order;
use Advancelearn\ManagePaymentAndOrders\Models\Payment;
use Advancelearn\ManagePaymentAndOrders\Models\Province;
use Advancelearn\ManagePaymentAndOrders\Models\Shipping;
use Advancelearn\ManagePaymentAndOrders\Observers\AdmUserObserver;
use App\Models\User;
use Illuminate\Support\ServiceProvider;


class ManagePayOrderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom($this->basePath('routes/api.php'));
        $this->loadMigrationsFrom($this->basePath('database/migrations'));

        $this->publishes([
            $this->basePath('database/migrations') => database_path('migrations')
        ], 'AdvanceLearnManagePayAndOrder-migrations');

        $this->publishes([
            $this->basePath('database/seeders/') => database_path('seeders'),
        ], 'AdvanceLearnManagePayAndOrder-seeds');

        User::Observe(AdmUserObserver::class);

    }

    public function register()
    {
        $bindings = [
            'order' => Order::class,
            'orderFunction' => OrderFunction::class,
            'paymentFunction' => PaymentFunction::class,
            'shipping' => Shipping::class,
            'address' => Address::class,
            'inventory' => Inventory::class,
            'audit' => Audit::class,
            'payment' => Payment::class,
            'city' => City::class,
            'province' => Province::class,
        ];

        foreach ($bindings as $key => $class) {
            $this->app->bind($key, function () use ($class) {
                return new $class();
            });
        }
    }

    protected function basePath($path = ""): string
    {
        return __DIR__ . "/../" . $path;
    }
}
