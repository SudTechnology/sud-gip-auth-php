# Sud GIP Auth PHP SDK
## Introduction
The Sud GIP Auth PHP SDK is a PHP library designed for implementing the Sud GIP authentication system, with support for JWT (JSON Web Token) authentication. This SDK provides functions such as generating authentication codes, server-to-server tokens (SSTokens), verifying tokens, and retrieving user IDs, making it convenient for developers to integrate Sud GIP's authentication services into PHP projects.
## Features
- Token Generation: Supports generating authentication codes and SSTokens, with the ability to set expiration durations.
- Token Verification: Verifies the validity of authentication codes and SSTokens.
- User ID Retrieval: Retrieves user IDs using authentication codes or SSTokens.
## Requirements and migration to 2.0
- PHP 8.0 or later and firebase/php-jwt ^7.0 are required. PHP 7.4 is no longer supported.
- HS256 requires an AppSecret of at least 32 bytes. Use your existing Sud-issued secret unchanged if it meets this requirement. Do not pad, hash, or replace it locally: signing and verifying services must use the same bytes. Contact the credential issuer to coordinate rotation if it is too short; rotation invalidates tokens signed with the old secret.
- The constructor throws InvalidArgumentException for an invalid/short secret. Token creation failures throw RuntimeException with the original exception attached.
- Token format, HS256 algorithm, claims, and public method names remain unchanged. Existing unexpired tokens with a sufficient-length unchanged key remain compatible, subject to JWT 7 validation.
- Token and decoded-claim logging has been removed.

Upgrade consumers with `composer require sudtechnology/sud-gip-auth-php:^2.0 -W` after the stable release is published.

## Development
Run `composer install`, `composer validate --strict`, `composer test`, and `composer audit`.

## Installation
You can install this SDK using Composer. Run the following command in your project's root directory:
```bash
    composer require sudtechnology/sud-gip-auth-php
```

## Usage
### Initialization
First, you need to instantiate the SudGIPAuth class and pass in your application ID and application secret:
```php
<?php
require 'vendor/autoload.php';

use Sud\Gip\Api\SudGIPAuth;

$appId = 'your_app_id';
$appSecret = getenv('SUD_APP_SECRET'); // Existing Sud-issued secret, at least 32 bytes

$auth = new SudGIPAuth($appId, $appSecret);
```

### Get Authentication Code
```php
$uid = 'user_id';
$code = $auth->getCode($uid);

echo "Code: " . $code->code . "\n";
echo "Expire Date: " . $code->expireDate . "\n";
```

### Get Server-to-Server Token (SSToken)
```php
$uid = 'user_id';
$ssToken = $auth->getSSToken($uid);

echo "SSToken: " . $ssToken->token . "\n";
echo "Expire Date: " . $ssToken->expireDate . "\n";
```

### Get User ID by Authentication Code
```php
$code = 'your_auth_code';
$uidResponse = $auth->getUidByCode($code);

if ($uidResponse->isSuccess) {
    echo "User ID: " . $uidResponse->uid . "\n";
} else {
    echo "Error Code: " . $uidResponse->errorCode . "\n";
}
```

### Get User ID by SSToken
```php
$ssToken = 'your_sstoken';
$uidResponse = $auth->getUidBySSToken($ssToken);

if ($uidResponse->isSuccess) {
    echo "User ID: " . $uidResponse->uid . "\n";
} else {
    echo "Error Code: " . $uidResponse->errorCode . "\n";
}
```

## Error Codes
| Error Code | Description          |
|------------|----------------------|
| 0          | Success              |
| 1001       | Token creation failed|
| 1002       | Token verification failed |
| 1003       | Token decoding failed |
| 1004       | Token is invalid     |
| 1005       | Token has expired    |
| 1101       | App data is invalid  |
| 9999       | Unknown error        |


## License
This project is licensed under the MIT License. See the LICENSE file for details.