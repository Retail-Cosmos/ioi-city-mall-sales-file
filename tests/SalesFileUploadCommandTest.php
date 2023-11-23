<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RetailCosmos\IoiCityMallSalesFile\Services\SalesFileUploaderService;

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

it('uploads sales files to SFTP server', function () {

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
        ]);

    Storage::fake('local');

    $storage = Storage::disk('local');

    $filePath = 'pending_to_upload/some-file.txt';

    $storage->put($filePath, 'some content goes here');

    $config = config('ioi-city-mall-sales-file');

    $serviceMock = Mockery::mock(SalesFileUploaderService::class);

    $serviceMock->shouldReceive('uploadFile')->withArgs([$config, 'pending_to_upload/some-file.txt'])->andReturnNull();

    $this->app->instance(SalesFileUploaderService::class, $serviceMock);

    Artisan::call('upload:ioi-city-mall-sales-files');

    $output = Artisan::output();

    expect($output)->toContain("Uploading File {$filePath} to SFTP Server");
    expect($output)->toContain("File {$filePath} has been uploaded to SFTP Server");
    expect($output)->toContain("Moving file to {$filePath} uploaded folder");
    expect($output)->toContain("File {$filePath} uploaded successfully");
    expect($output)->toContain('Sales files uploaded successfully.');

    expect($storage->exists('uploaded/some-file.txt'))->toBeTrue();
});
