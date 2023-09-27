<?php

namespace Advancelearn\ManagePaymentAndOrders\Observers;

use App\Models\User;

class AdmUserObserver
{

    public function created(User $user)
    {
        $user->addresses()->forceCreate([
            'user_id' => $user->id,
            'adm_city_id' => null,
            'address' => 'digital_delivery',
            'description' => 'digital_delivery',
        ]);
    }


}
