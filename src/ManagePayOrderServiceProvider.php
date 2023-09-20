<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Advancelearn\ManagePaymentAndOrders;

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
    }

    public function register()
    {

    }

    protected function basePath($path = ""): string
    {
        return __DIR__ . "/../" . $path;
    }
}
