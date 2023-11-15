<?php

use Illuminate\Support\Facades\Artisan;

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
