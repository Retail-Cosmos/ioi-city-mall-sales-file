<?php

use Illuminate\Support\Facades\Artisan;

it('config file is publishable', function () {

    Artisan::call('vendor:publish', ['--tag' => 'ioi-city-mall-sales-file-config']);

    $configPath = config_path('ioi-city-mall-sales-file.php');

    expect(file_exists($configPath))->toBeTrue();
});

it('has all required configuration variables', function () {

    $config = config('ioi-city-mall-sales-file');

    expect($config)->toBeArray();

    $requiredKeys = [
        'stores',
        'disk_to_use',
        'log_channel_for_file_generation',
        'sftp',
        'log_channel_for_file_upload',
        'notifications',
        'first_file_generation_date',
    ];

    foreach ($requiredKeys as $key) {
        expect(array_key_exists($key, $config))->toBeTrue();
    }
});
