# PayCow PHP SDK

Server-side PHP client for the PayCow non-custodial USDT payment API.

## Requirements

- PHP 7.4 or newer
- cURL, JSON, and Hash extensions
- PayCow merchant API key and secret

## Installation

Download and extract the SDK archive into your application, then load the included autoloader.

```php
require_once __DIR__ . '/paycow-php-sdk/autoload.php';
```

## Usage

```php
use PayCow\PayCowClient;

$paycow = new PayCowClient([
    'api_key' => getenv('PAYCOW_API_KEY'),
    'api_secret' => getenv('PAYCOW_API_SECRET'),
]);

$payment = $paycow->payments()->create([
    'userId' => 'customer-2048',
    'amount' => '50.00',
    'network' => 'TRC20',
    'idempotencyKey' => 'topup-2048-001',
]);

echo $payment->getPaymentUrl();
```

See https://paycow.net/docs/sdk for the complete reference.
