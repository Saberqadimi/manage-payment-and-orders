<?php /** @noinspection ALL */

namespace Advancelearn\ManagePaymentAndOrders\Models\Functions;

use Advancelearn\ManagePaymentAndOrders\Transformer\PaymentResource;
use Advancelearn\ManagePaymentAndOrders\Transformer\SinglePaymentResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PaymentFunction
{

    /**
     * Retrieve a paginated list of payments.
     *
     * @param int $paginateCount
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getPayments(int $paginateCount = 6)
    {
        return $this->queryPayments()
            ->latest()
            ->paginate($paginateCount);
    }

    /**
     * Retrieve a single payment by ID.
     *
     * @param $paymentId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function singlePayment($paymentId)
    {
        return $this->queryPayments()
            ->findOrfail($paymentId);
    }

    /**
     * Retrieve a paginated list of payments for the logged-in user.
     *
     * @param int $paginateCount
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paymentsOfTheLoggedInUser(int $paginateCount = 6)
    {
        return SinglePaymentResource::collection(
            $this->queryPaymentsForLoggedInUser()
                ->latest()
                ->paginate($paginateCount)
        );
    }

    /**
     * Retrieve a single payment for the logged-in user by ID.
     *
     * @param $paymentId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function SinglePaymentsOfTheLoggedInUser($paymentId)
    {
        return $this->queryPaymentsForLoggedInUser()
            ->findOrFail($paymentId);
    }

    /**
     * Common query to fetch payments with relationships.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryPayments()
    {
        return app('payment')
            ->with('order');
    }

    /**
     * Common query to fetch payments for the logged-in user with relationships.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryPaymentsForLoggedInUser()
    {
        return $this->queryPayments()
            ->whereHas('order.address', fn($query) => $query->where('user_id', Auth::id()));
    }

}
