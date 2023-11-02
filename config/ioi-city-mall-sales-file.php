<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store Configurations
    |--------------------------------------------------------------------------
    |
    | Define store-specific settings here. Each store should have an entry
    | containing the store identifier, machine ID, and SST registration status.
    |
    */
    'stores' => [
        [
            'identifier' => 'store_1',
            'machine_id' => env('IOI_CITY_MALL_MACHINE_ID'),
            'sst_registered' => true,
        ],
        // Add more store configurations as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | Define the storage disk to use for storing sales data files. You can specify
    | the name of the configured storage disk here.
    |
    */
    'disk_to_use' => '',

    /*
    |--------------------------------------------------------------------------
    | Log Channel for File Generation
    |--------------------------------------------------------------------------
    |
    | Specify the log channel to use for recording file generation activity. You
    | can set the log channel name to keep track of file generation events.
    |
    */
    'log_channel_for_file_generation' => '',

    /*
    |--------------------------------------------------------------------------
    | SFTP Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the SFTP server connection details. Provide the IP address,
    | port, username, and password for uploading files to the server.
    |
    */
    'sftp' => [
        'ip_address' => env('IOI_CITY_MALL_SFTP_IP_ADDRESS'), // SFTP server IP address
        'port' => env('IOI_CITY_MALL_SFTP_PORT'), // SFTP server port
        'username' => env('IOI_CITY_MALL_SFTP_USERNAME'), // SFTP username
        'password' => env('IOI_CITY_MALL_SFTP_PASSWORD'), // SFTP password
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channel for File Upload
    |--------------------------------------------------------------------------
    |
    | Set the log channel for recording file upload activities. You can specify
    | the log channel name to keep track of file upload events.
    |
    */
    'log_channel_for_file_upload' => '',

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure the notification settings, including the notification name and
    | email address for sending sales data-related notifications.
    |
    */
    'notifications' => [
        'name' => env('IOI_CITY_MALL_SALES_FILE_NOTIFICATION_NAME'), // Notification name
        'email' => env('IOI_CITY_MALL_SALES_FILE_NOTIFICATION_EMAIL'), // Notification email
    ],

    /*
    |--------------------------------------------------------------------------
    | First File Generation Date
    |--------------------------------------------------------------------------
    |
    | This setting is used to calculate the Batch ID for generated files. It specifies
    | the first date to start counting from for the Batch ID calculation.
    |
    */
    'first_file_generation_date' => '',

];