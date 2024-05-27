<?php

namespace RetailCosmos\IoiCityMallSalesFile\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RetailCosmos\IoiCityMallSalesFile\IoiCityMallSalesFileServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            IoiCityMallSalesFileServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('ioi-city-mall-sales-file.notifications.name', 'Admin');
        config()->set('ioi-city-mall-sales-file.notifications.email', 'admin@example.com');
        config()->set('ioi-city-mall-sales-file.notifications.trigger_failure_notifications_only', false);
        config()->set('database.default', 'testing');
    }

    public function assertNotificationDetails(
        $notification,
        $notifiable,
        string $expectedEmail,
        string $expectedStatus,
        string $expectedReceiverName
    ): bool {
        $routeKeys = array_keys($notifiable->routes['mail']);
        if (count($routeKeys) > 0) {
            $email = $routeKeys[0];

            return $email === $expectedEmail
                && $notification->getStatus() === $expectedStatus
                && $notification->getReceiverName() === $expectedReceiverName;
        }

        return false;
    }
}
