<?php

namespace Advancelearn\ManagePaymentAndOrders\Tests;

use Advancelearn\ManagePaymentAndOrders\ManagePayOrderServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'package-learn');
        $app['config']->set('database.connections.package-learn', [
            'driver' => 'mysql',
            'host' => '127.0.0.1', // Replace with your database host
            'database' => 'package-learn', // Replace with your database name
            'username' => 'root', // Replace with your database username
            'password' => '', // Replace with your database password
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            ManagePayOrderServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
       //
    }
}