# Sud GIP Auth PHP SDK
## Introduction
The Sud GIP Auth PHP SDK is a PHP library designed for implementing the Sud GIP authentication system, with support for JWT (JSON Web Token) authentication. This SDK provides functions such as generating authentication codes, server-to-server tokens (SSTokens), verifying tokens, and retrieving user IDs, making it convenient for developers to integrate Sud GIP's authentication services into PHP projects.
## Features
- Token Generation: Supports generating authentication codes and SSTokens, with the ability to set expiration durations.
- Token Verification: Verifies the validity of authentication codes and SSTokens.
- User ID Retrieval: Retrieves user IDs using authentication codes or SSTokens.
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
$appSecret = 'your_app_secret';

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