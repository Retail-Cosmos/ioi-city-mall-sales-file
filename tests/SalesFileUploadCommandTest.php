<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RetailCosmos\IoiCityMallSalesFile\Notifications\SalesFileUploadNotification;
use RetailCosmos\IoiCityMallSalesFile\Services\SalesFileUploaderService;

beforeEach(function (): void {
    $this->email = config('ioi-city-mall-sales-file.notifications.email');
    $this->receiverName = config('ioi-city-mall-sales-file.notifications.name');

    Notification::fake();
});

describe('Configuration Checks', function () {

    it('will not execute the command if file upload flag is disabled', function () {

        config()->set('ioi-city-mall-sales-file.enable_file_upload', false);

        Artisan::call('upload:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('File upload is disabled. Please check your .env file.');

        Notification::assertNothingSent();

    });

    it('will throw error if notifications.trigger_failure_notifications_only is not present', function () {

        config()->set('ioi-city-mall-sales-file.notifications', [
            'email' => 'some@email.com',
        ]);

        Artisan::call('upload:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('An error occurred while uploading sales files: Please indicate whether to enable only failure notifications');

        Notification::assertNothingSent();

    });

});

describe('Configuration Checks with Notifications', function () {

    it('throws an error if disk_to_use is missing or empty', function () {

        config()->offsetUnset('ioi-city-mall-sales-file.disk_to_use');

        Artisan::call('upload:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('The disk_to_use key in configuration file is not set. Please ensure it is properly configured.');

    });

    it('throws an error if SFTP array is missing or empty', function () {

        config()->offsetUnset('ioi-city-mall-sales-file.sftp');

        Artisan::call('upload:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('SFTP Config array is required.');

    });

    it('throws an error if SFTP array values are missing or empty', function (string $sftpVar, string $sftpVarMessage) {
        config()->set('ioi-city-mall-sales-file.sftp', [
            'ip_address' => fake()->ipv4(),
            'port' => fake()->randomNumber(3),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'path' => fake()->filePath(),
        ]);

        config()->offsetUnset('ioi-city-mall-sales-file.sftp.'.$sftpVar);

        Artisan::call('upload:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain($sftpVarMessage);

    })->with([
        'IP Address' => ['ip_address', 'SFTP Config array must have a valid IP Address as host.'],
        'Port' => ['port', 'SFTP Config array must have a Port Configured Properly.'],
        'Username' => ['username', 'SFTP Config array must have a valid username.'],
        'Password' => ['password', 'SFTP Config array must have a valid password.'],
        'Path' => ['path', 'SFTP Config array must have a valid file(s) upload path.'],
    ]);

    afterEach(function (): void {
        Notification::assertSentOnDemand(
            SalesFileUploadNotification::class,
            function ($notification, $channels, $notifiable) {
                return $this->assertNotificationIsSent(
                    notification: $notification,
                    notifiable: $notifiable,
                    expectedEmail: $this->email,
                    expectedStatus: 'error',
                    expectedReceiverName: $this->receiverName
                );
            }
        );
    });
});

describe('Success Scenarios', function () {

    beforeEach(function (): void {
        config()->set('ioi-city-mall-sales-file',
            [
                'disk_to_use' => 'local',
                'sftp' => [
                    'ip_address' => '127.0.0.0',
                    'port' => 22,
                    'username' => 'mock-username',
                    'password' => 'mock-password',
                    'path' => '/path/to/sftp/upload',
                ],
                'log_channel_for_file_upload' => 'local',
                'notifications' => [
                    'name' => 'Admin',
                    'email' => 'admin@example.com',
                    'trigger_failure_notifications_only' => false,
                ],
                'enable_file_upload' => true,
            ]);

        Storage::fake('local');
    });

    it('uploads sales files to SFTP server', function () {

        $storage = Storage::disk('local');

        $filePath = 'pending_to_upload/some-file.txt';

        $storage->put($filePath, 'some content goes here');

        $config = config('ioi-city-mall-sales-file');

        $serviceMock = Mockery::mock(SalesFileUploaderService::class);

        $serviceMock->shouldReceive('uploadFile')->withArgs([$config, $filePath])->andReturnNull();

        $this->app->instance(SalesFileUploaderService::class, $serviceMock);

        Artisan::call('upload:ioi-city-mall-sales-files');

        $output = Artisan::output();

        expect($output)->toContain("Uploading File {$filePath} to SFTP Server");
        expect($output)->toContain("File {$filePath} has been uploaded to SFTP Server");
        expect($output)->toContain("Moving file to {$filePath} uploaded folder");
        expect($output)->toContain("File {$filePath} uploaded successfully");
        expect($output)->toContain('Sales files uploaded successfully.');

        expect($storage->exists('uploaded/some-file.txt'))->toBeTrue();

        Notification::assertSentOnDemand(
            SalesFileUploadNotification::class,
            function ($notification, $channels, $notifiable) {
                return $this->assertNotificationIsSent(
                    notification: $notification,
                    notifiable: $notifiable,
                    expectedEmail: $this->email,
                    expectedStatus: 'success',
                    expectedReceiverName: $this->receiverName
                );
            }
        );
    });

    it('uploads sales files to SFTP server without sending success notification when trigger_failure_notifications_only is true', function () {

        $storage = Storage::disk('local');

        $filePath = 'pending_to_upload/some-file.txt';

        $storage->put($filePath, 'some content goes here');

        config()->set('ioi-city-mall-sales-file.notifications.trigger_failure_notifications_only', true);

        $config = config('ioi-city-mall-sales-file');

        $serviceMock = Mockery::mock(SalesFileUploaderService::class);

        $serviceMock->shouldReceive('uploadFile')->withArgs([$config, $filePath])->andReturnNull();

        $this->app->instance(SalesFileUploaderService::class, $serviceMock);

        Artisan::call('upload:ioi-city-mall-sales-files');

        $output = Artisan::output();

        expect($output)->toContain("Uploading File {$filePath} to SFTP Server");
        expect($output)->toContain("File {$filePath} has been uploaded to SFTP Server");
        expect($output)->toContain("Moving file to {$filePath} uploaded folder");
        expect($output)->toContain("File {$filePath} uploaded successfully");
        expect($output)->toContain('Sales files uploaded successfully.');

        expect($storage->exists('uploaded/some-file.txt'))->toBeTrue();

        Notification::assertNothingSent();
    });

    it('shows no sales records found for upload', function () {

        $storage = Storage::disk('local');

        expect($storage->files('pending_to_upload'))->toBeEmpty();

        Artisan::call('upload:ioi-city-mall-sales-files');

        $output = Artisan::output();

        expect($output)->toContain('No sales files found for upload');

        expect($storage->files('uploaded'))->toBeEmpty();

        Notification::assertSentOnDemand(
            SalesFileUploadNotification::class,
            function ($notification, $channels, $notifiable) {
                return $this->assertNotificationIsSent(
                    notification: $notification,
                    notifiable: $notifiable,
                    expectedEmail: $this->email,
                    expectedStatus: 'info',
                    expectedReceiverName: $this->receiverName
                );
            }
        );
    });

    it('file upload works even if the notification config is not set.', function () {
        config()->set('ioi-city-mall-sales-file.notifications.email', null);
        config()->set('ioi-city-mall-sales-file.notifications.name', null);

        Artisan::call('upload:ioi-city-mall-sales-files');

        $output = Artisan::output();
        expect($output)->toContain('No sales files found for upload');

        Notification::assertNothingSent();
    });
});

afterEach(function (): void {
    Mockery::close();
});
