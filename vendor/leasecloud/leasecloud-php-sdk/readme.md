# LeaseCloud PHP SDK

[![CircleCI](https://circleci.com/gh/LeaseCloud/leasecloud-php-sdk/tree/master.svg?style=shield)](https://circleci.com/gh/LeaseCloud/leasecloud-php-sdk/tree/master) [![Latest Stable Version](https://poser.pugx.org/leasecloud/leasecloud-php-sdk/version)](https://packagist.org/packages/leasecloud/leasecloud-php-sdk) [![License](https://poser.pugx.org/leasecloud/leasecloud-php-sdk/license.svg)](https://packagist.org/leasecloud/leasecloud-php-sdk)


Sign up for a LeaseCloud account at https://www.leasecloud.se

## Requirements

PHP 5.6 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require leasecloud/leasecloud-php-sdk
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

```php
require_once('vendor/autoload.php');
```

## Dependencies

The SDK require the following extension in order to work properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
- [`json`](https://secure.php.net/manual/en/book.json.php)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Documentation

Please see [http://leasecloud.io](http://leasecloud.io) for up-to-date documentation about the underlying REST API.

## Using the SDK

### Getting your tariffs


```php
require_once('vendor/autoload.php');

LeaseCloud\LeaseCloud::setApiKey(API_KEY);
$config = LeaseCloud\Config::retrieve();
print_r($config);

Output:
stdClass Object (
    [financeCompany] => stdClass Object (
        [name] => "Montly Leasing",
        [logo] => "https://example.com/logo.svg"
    ),
    [tariffs] => Array
        (
            [0] => stdClass Object
                (
                    [months] => 24
                    [tariff] => 4.47
                )

            [1] => stdClass Object
                (
                    [months] => 36
                    [tariff] => 3.04
                )

        )

)
```
You are responsible for caching the tariffs object in your environment. Re-read the tariffs once every 24 hours.

### Calculating the monthly price

To determine the monthly price for a specific item in your ecommerce shop, use the helper function:

```php
$config = ... // Read from your cache
$monthlyPrice = Tariff::monthlyCost(7999, 36, $config->tariffs);
var_dump($monthlyPrice);

Output:
double(243.1696)
```

### Creating an order

```php
require_once('vendor/autoload.php');

/*
  See http://leasecloud.io/#create-a-new-order for complete set of fields
*/

$config = ... // Read from your cache
$tariff = LeaseCloud\Tariff::tariff($months, $config->tariffs);
$totalAmount = 9000; // 9000 SEK
$vat = 0.25;
$months = 36;
$monthlyAmount = LeaseCloud\Tariff::monthlyCost($totalAmount, $months, $config->tariffs);

$order = [
           "orderId"        => $orderId,      // Unique id from your ecommerce system
           "firstName"      => "Stan",
           "lastName"       => "Hunter",
            "customerIp"    => "..",          // Valid IP-number
            "months"        => $months,       // Length of leasing agreement
            "tariff"        => $tariff,       // Tariff used

            // All price data is entered as integers of the smallest
            // currency amount E.g. 1 SEK = 100 ören, 1 USD = 100 cent

            "totalAmount"   => (int)($totalAmount * 100),
            "VAT"           => (int)($totalAmount * $vat * 100),
            "monthlyAmount" => (int)($monthlyAmount * 100),
            ...
            ...
         ]

LeaseCloud\LeaseCloud::setApiKey(API_KEY);
$response = LeaseCloud\Order::create($order);

if (isset($response->errors)) {
    // Something went wrong!
    // Error details in $response->errors array
    print_r($response);
} else {
    // Success
}

```

### Checking order status

```php
require_once('vendor/autoload.php');

$orderId = 'abc123';
LeaseCloud\LeaseCloud::setApiKey(API_KEY);
$response = Order::status($orderId);
print_r($response);

Output:
stdClass Object
(
    [orderId] => abc123
    [status] => pending
)

```

### Cancel an order

If you need to cancel an order:

```php
require_once('vendor/autoload.php');

$orderId = 'abc123';
LeaseCloud\LeaseCloud::setApiKey(API_KEY);
$response = Order::cancel($orderId);
print_r($response);

Output:
stdClass Object
(
    [code] => 200
    [status] => success
)
```

### Mark an order as shipped

When an approved order is shipped, your ecommerce system should notify LeaseCloud:

```php
require_once('vendor/autoload.php');

$orderId = 'abc123';
LeaseCloud\LeaseCloud::setApiKey(API_KEY);
$response = Order::shipped($orderId);
print_r($response);

Output:
stdClass Object
(
    [code] => 204
    [status] => success
)

```

## Webhooks

Your applications can receive events from LeaseCloud using webhooks. Contact LeaseCloud support to set your webhook URL. You will also receive a WEBHOOK_SECRET to be used when validating the origin of requests to your webhook URL.

### Validating a webhook

```php
require_once('vendor/autoload.php');

LeaseCloud\Webook::setSecret(WEBHOOK_SECRET);
$signature = $_SERVER['LeaseCloud-Signature'];
$payload = file_get_contents('php://input');
$valid = LeaseCloud\Webook::validateSignature($signature, $payload);

if ($valid) {
	$json = json_decode($payload);
	$id   = $json->id;   //unique event id
	$type = $json->type; // name of the action
	$data = $json->data; // Additional fields
} else {
	die('You cheat!');
}

```
