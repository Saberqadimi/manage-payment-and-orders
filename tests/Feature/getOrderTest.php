<?php

namespace Advancelearn\ManagePaymentAndOrders\Tests\Feature;

use Advancelearn\ManagePaymentAndOrders\Models\Functions\OrderFunction;
use Advancelearn\ManagePaymentAndOrders\Models\Order;
use Advancelearn\ManagePaymentAndOrders\Tests\TestCase;
use Advancelearn\ManagePaymentAndOrders\Transformer\OrderResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mockery;

class getOrderTest extends TestCase
{

    protected $orderFunction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderFunction = new OrderFunction();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testGetOrdersReturnsAnonymousResourceCollection()
    {
        // $this->withExceptionHandling();
        // // Create a mock instance of OrderFunction
        // $orderFunctionMock = Mockery::mock(OrderFunction::class);

        // // Mock the 'order' and 'OrderResource' dependencies
        // $orderMock = Mockery::mock(Order::class);
        // $orderResourceMock = Mockery::mock(OrderResource::class);

        // // Bind the mock instances in the container
        // $this->app->instance(OrderFunction::class, $orderFunctionMock);
        // $this->app->instance(Order::class, $orderMock);
        // $this->app->instance(OrderResource::class, $orderResourceMock);

        // // Create a mock paginator
        // $paginator = new LengthAwarePaginator([], 0, 12);

        // // Mock the 'latest' and 'paginate' methods on the 'Order' class
        // $orderMock->shouldReceive('latest')->once()->andReturnSelf();
        // $orderMock->shouldReceive('paginate')->once()->with(12)->andReturn($paginator);

        // // Set expectations for the getOrders method
        // $orderFunctionMock->shouldReceive('getOrders')->once()->andReturn($paginator);

        // // Call the getOrders method on the mock instance and assert the return type
        // $result = $this->orderFunction->getOrders();
        // $this->assertInstanceOf(AnonymousResourceCollection::class, $result);
    }


}