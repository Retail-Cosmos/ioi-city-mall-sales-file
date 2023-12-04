# The IOI City Mall Sales File Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/retail-cosmos/ioi-city-mall-sales-file.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/ioi-city-mall-sales-file)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/ioi-city-mall-sales-file/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/retail-cosmos/ioi-city-mall-sales-file/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/ioi-city-mall-sales-file/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/retail-cosmos/ioi-city-mall-sales-file/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/retail-cosmos/ioi-city-mall-sales-file.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/ioi-city-mall-sales-file)

The IOI City Mall Sales File Generator is a Laravel package that simplifies the creation of daily sales data files for IOI City Mall stores. It seamlessly integrates into Laravel projects, streamlining data generation for retail management.

## Installation

1. Install the package via composer:

```bash
composer require retail-cosmos/ioi-city-mall-sales-file
```

2. Publish the config file with:

```bash
php artisan vendor:publish --tag="ioi-city-mall-sales-file-config"
```

3. Please set values for all the options in the config file.


## Usage

The functionality is divided into two parts. The first part is the [File Generation](#file-generation) and the second part is the [File Upload (SFTP)](#file-upload-sftp).

### File Generation

Please follow these steps for the file generation.

1. Add a [scheduler](https://laravel.com/docs/10.x/scheduling) in your Laravel project to call the command `generate:ioi-city-mall-sales-files` daily at midnight. It generates the sales file for the previous day for each store as returned from the application.

```php
$schedule->command('generate:ioi-city-mall-sales-files')->daily();
```

> [!TIP]
> If you wish to generate a specific sales file, you may pass the following options to the command:
>    - `date` - Date in the YYYY-MM-DD format to generate a sales file for a specific date.
>    - `store_identifier` - To generate a sales file for a specific store only.

2. Create a new class `IOICityMallSalesDataService` in the `App/Services` namespace and add a `storesList()` method in it. It should return the collection of stores. The keys need to be:
    - `store_identifier` (String)
    - `machine_id` (String. Machine ID as received from the IOI City Mall)
    - `sst_registered` (Boolean)

When you pass a `store_identifier` as an option to the sales file generation command, the package passes it as a parameter to the `storesList()` method.

<details>
<summary>Click here to see the example code for the storesList() method</summary>

```php
public function storesList(string $storeIdentifier = null): Collection
{
    return collect([
        [
            'store_identifier' => 'my_store_46592',
            'machine_id' => 48623791,
            'sst_registered' => false,
        ],
        [
            'store_identifier' => 'my_store_97314',
            'machine_id' => 37196428,
            'sst_registered' => true,
        ],
    ]);
}
```
</details>

> [!TIP]
> If you return a blank collection, the command does not generate any sales file and just logs a message.

3. Add a `salesData()` method in the `IOICityMallSalesDataService` class. The package will call this method to get the sales data. The method receives the following parameters:
    - `store_identifier` (string) - as returned from the `storesList()` method or passed to the sales file generation command as an option.
    - `date` (string) - YYYY-MM-DD format

This is the main part of the implementation. You need to add code for this method in a way that it fetches the sales data for the specified store for the specified date and returns a collection of sales. The keys need to be:
```
    - 'happened_at' (Date and time of the sale)
    - 'net_amount' (Total amount of the sale after discount and before SST)
    - 'discount' (Total discount amount of the sale. Item-wise division is not needed)
    - 'SST'
    - 'payments': (Amount of the payment type after discount and before SST)
        - 'cash'
        - 'tng'
        - 'visa'
        - 'mastercard'
        - 'amex'
        - 'voucher'
        - 'others'
```


> [!IMPORTANT]
> You can use `RetailCosmos\IoiCityMallSalesFile\Enums\PaymentType` enum for keys of the payments.

<details>
<summary>Click here to see the example code for the salesData() method</summary>

```php
public function salesData(string $storeIdentifier, string $date): Collection
{
    return collect([
        [
            'happened_at' => '2024-01-20 15:41:37',
            'net_amount' => 100,
            'discount' => 20,
            'SST' => 6,
            'payments' => [
                PaymentType::CASH->value => 50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 30,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => 20,
            ],
        ],
        [
            'happened_at' => '2024-01-20 16:18:09',
            'net_amount' => -50,
            'discount' => -5,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => -50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 0,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => 0,
            ],
        ],
    ]);
}
```
</details>

> [!TIP]
> Take a note that you need to return sales for all the counters/registers of a store. The Mall expects the sales of all the counters to be combined in the file.

:rocket: And that is it. The scheduler calls the command every day and the package generates a sales file and puts it into the the filesystem as per the config. Next, you may follow the steps for the [File Upload](#file-upload-sftp) part.

#### Disable File Generation

The package provides an .env variable `IOI_CITY_MALL_ENABLE_FILE_GENERATION` in case you wish to disable the file generation. If this .env variable is set to `false`, the file will not be generated even when the command is run.

#### Notes about generated sales files
- The generated files are stored as per your config disk. There are two directories inside it: `pending_to_upload` and `uploaded` (These two directories are auto-generated if they don’t exist)
- The complete log of the generated files gets prepared and stored as per your log channel config. An email notification is also sent as per your notifications config.

#### Note about Sale Returns

You may provide all the numbers in negative in case of refunds. As per the specifications, the refund amount should be deducted from the sales amount so it will automatically be taken care of during the grouping of records by the package.

#### Note about Batch IDs

`Batch ID` is managed by the package. As per the mall specifications, it needs to be a sequential number starting from 1 for the first file generated. You may set the `first_file_generation_date` in the config. The package counts the days from that date to calculate the Batch ID every time. If the date is not set in the config, an exception will be thrown.


### File Upload (SFTP)

There is only one step to start the file uploads.

Add a [scheduler](https://laravel.com/docs/10.x/scheduling) in your Laravel project to call the command `upload:ioi-city-mall-sales-files` daily at 12:30 AM. It uploads all the files from the `pending_to_upload` directory via SFTP as per your config and moves those files to the `uploaded` directory.

```php
$schedule->command('upload:ioi-city-mall-sales-files')->dailyAt('00:30');
```

The complete log of the uploaded files gets prepared and stored as per your log channel config. An email notification is also sent as per your notifications config.

#### Disable File Upload (SFTP)

The package provides an .env variable `IOI_CITY_MALL_ENABLE_FILE_UPLOAD` in case you wish to disable the file upload. If this .env variable is set to `false`, the file will not be uploaded even when the command is run.


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Harshvardhan Sharma](https://github.com/hvsharma63)
- [Gaurav Makhecha](https://github.com/gauravmak)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
