<?php

namespace Advancelearn\ManagePaymentAndOrders;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    protected $namespace = 'Advancelearn\ManagePaymentAndOrders\Http\Controllers';

    public function map()
    {
        $this->routes(function () {
            Route::namespace($this->namespace)
                ->group(__DIR__ . '/../routes/api.php');

//        Route::namespace($this->namespace)
//            ->prefix('api')
//            ->group(__DIR__ . '/../routes/api.php');
        });
    }
}
