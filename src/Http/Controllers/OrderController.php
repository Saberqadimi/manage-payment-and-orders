<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
namespace Advancelearn\ManagePaymentAndOrders\Http\Controllers;
use Advancelearn\ManagePaymentAndOrders\Http\Request\OrderCreateRequest;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    public function index()
    {
        dd('dededede');
    }

    public function store(OrderCreateRequest $request)
    {


    }

}
