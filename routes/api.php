<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/*
|--------------------------------------------------------------------------
| Api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register api routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api:auth" middleware group. Now create something great!
|
*/

use Advancelearn\ManagePaymentAndOrders\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::apiResource('/adm-orders', OrderController::class);

});



