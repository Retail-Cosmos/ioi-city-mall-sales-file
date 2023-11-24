<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | Define the storage disk to use for storing sales data files. You can specify
    | the name of the configured storage disk here.
    |
    */
    'disk_to_use' => env('IOI_CITY_MALL_STORAGE_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Log Channel for File Generation
    |--------------------------------------------------------------------------
    |
    | Specify the log channel to use for recording file generation activity. You
    | can set the log channel name to keep track of file generation events.
    |
    */
    'log_channel_for_file_generation' => env('IOI_CITY_MALL_FILE_GENERATION_LOG_CHANNEL', 'stack'),

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
        'path' => env('IOI_CITY_MALL_SFTP_FILE_UPLOAD_PATH'),
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
    'log_channel_for_file_upload' => env('IOI_CITY_MALL_FILE_UPLOAD_LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure the notification settings for email notifications, including
    | the receiver's name and email address for sending sales data-related notifications.
    |
    */
    'notifications' => [
        'name' => env('IOI_CITY_MALL_SALES_FILE_NOTIFICATION_NAME'), // Receiver's Name
        'email' => env('IOI_CITY_MALL_SALES_FILE_NOTIFICATION_EMAIL'), // Receiver's E-mail
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
    'first_file_generation_date' => '2023-01-01',

    /*
    |--------------------------------------------------------------------------
    | "Enable File Generation" Flag
    |--------------------------------------------------------------------------
    |
    | This flag is used to enable or disable Sales File Generation. By default, it will
    | be true.
    |
    */
    'enable_file_generation' => env('IOI_CITY_MALL_ENABLE_FILE_GENERATION', true),

    /*
    |--------------------------------------------------------------------------
    | "Enable File Upload" Flag
    |--------------------------------------------------------------------------
    |
    | This flag is used to enable or disable Sales File Upload. By default, it will
    | be true.
    |
    */
    'enable_file_upload' => env('IOI_CITY_MALL_ENABLE_FILE_UPLOAD', true),

];
