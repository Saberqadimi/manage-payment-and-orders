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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {

    Route::get('/adm-orders', function (Request $request) {

        /**get */
//        return app('orderFunction')->getOrders(); // get Order List for AdminPanel
//        return app('orderFunction')->singleOrder(5); // get singleOrder for AdminPanel

//        return app('orderFunction')->ordersOfTheLoggedInUser(); // get user Authenticated Order list
//        return app('orderFunction')->SingleOrderOfTheLoggedInUser(5); //get user Authenticated singleOrder


        /** store new order $items ******************************************/
        $items = [
            0 => [
                'quantity' => 2,
                "inventory_id" => 1
            ]

        ];
        $shippingId = $request->shippingId; //can be null
        $addressId = (int)$request->addressId;
        $newOrder = app('orderFunction')->store($shippingId, $addressId, "test from create new order", $items);
//
        return $newOrder;
        /*******************************************************************/
        /** show single order */

//        $order = app('orderFunction')->show(14);
//
//        return $order;
        /**************************************/
        $items = [
            0 => [
                'quantity' => 1,
                "inventory_id" => 1,
                "price" => 168000//It can be Nal
            ]
        ];
        $shippingId = $request->shippingId; //can be null
        $addressId = $request->addressId;
        $auditId = app('audit')::find(2)->toArray();
        $orderId = 9;
        $update = app('orderFunction')->update($shippingId, $addressId, "update order for test", "2023-09-28 10:01:03", $items, $auditId['id'], $orderId);
        #params => shippingId , $addressId , $description , $shippingDate , $items , $auditID , $orderId

        return $update;

//        $delete = app('orderFunction')->destroyByUser($orderId);
//        return $delete;
//
//        $delete = app('orderFunction')->destroyByAdmin($orderId);
//        return $delete;

    });

    Route::get('/payment-test', function () {

//       return app('paymentFunction')->getPayments(); // get PaymentList for AdminPanel

//       return app('paymentFunction')->singlePayment(1); //get singlePayment for AdminPanel


//       return app('paymentFunction')->paymentsOfTheLoggedInUser(); //get user Authenticated payment list

//       return app('paymentFunction')->SinglePaymentsOfTheLoggedInUser(1); //send $paymentId

    });

});



