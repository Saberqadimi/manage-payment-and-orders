<?php

namespace Database\Seeders;

use Advancelearn\ManagePaymentAndOrders\Models\Audit;
use Illuminate\Database\Seeder;

class AuditSeeder extends Seeder
{

    public function run()
    {
        $audits = [
            ['title' => 'Initial registration'],
            ['title' => 'Final registration'],
            ['title' => 'Inventory confirmation'],
            ['title' => 'Ready to send'],
            ['title' => 'Posted'],
            ['title' => 'Canceled by admin'],
            ['title' => 'Canceled by user'],
            ['title' => 'delivered'],
            ['title' => 'Edit'],
            ['title' => 'Paid'],
            ['title' => 'Delete the system'],
        ];

        foreach ($audits as $audit) {
            Audit::create($audit);
        }
    }
}
