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


## API request signing and callback verification

`Sud\Gip\Api\RequestSigner` provides HMAC-SHA1 signing independently of JWT
operations. These helpers are available starting with SDK 2.1.0.

```php
use Sud\Gip\Api\RequestSigner;

$signer = new RequestSigner($appId, $appSecret);

// Serialize once. Sign these bytes and send the same string unchanged.
$rawBody = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
$timestamp = (string) (int) floor(microtime(true) * 1000);
$nonce = bin2hex(random_bytes(16));
$authorization = $signer->generateAuthorization($timestamp, $nonce, $rawBody);

$curl = curl_init($apiUrl);
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $rawBody,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: ' . $authorization,
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
// Execute the request and handle HTTP/network errors in your application.
```

The outbound example requires PHP's cURL extension. The signer itself performs
no HTTP I/O and requires no additional package. Use the issued secret unchanged;
this HMAC-SHA1 component does not impose the JWT HS256 32-byte minimum.

`generateAuthorization()` returns a complete single-line header value:

```text
Sud-Auth app_id="...",timestamp="...",nonce="...",signature="..."
```

For a signature alone, use `$signer->generateSignature($timestamp, $nonce, $rawBody)`.
It returns 40 lowercase hexadecimal characters. Metadata must be non-empty
printable ASCII without spaces, quotes, backslashes, or commas. Invalid credentials
or signing metadata throw `InvalidArgumentException` without exposing inputs.

For callbacks, read the `Sud-AppId`, `Sud-Timestamp`, `Sud-Nonce`, and
`Sud-Signature` headers and the original body, then call:

```php
$valid = $signer->verifySignature(
    $callbackAppId,
    $timestamp,
    $nonce,
    $rawBody,
    $signature
);
if (!$valid) {
    // Return an HTTP 403 response before parsing the body.
    return;
}
$payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
```

`verifySignature()` checks that the callback application ID matches the configured
ID, rejects malformed metadata/signatures, and compares decoded digests in constant
time. It returns a boolean. HTTP header extraction, duplicate-header checks,
route selection, and the verification switch belong to the application.

**Sign and verify the ORIGINAL body bytes:**

```text
app_id + "\n" + timestamp + "\n" + nonce + "\n" + rawBody + "\n"
```

The final newline is mandatory. PHP strings preserve bytes, including binary
values. Use an empty string for an empty body. Never deserialize/reserialize JSON,
trim, normalize line endings, convert encodings, or decompress before verification.
A successful callback signature check does not replace SDK JWT verification.

The helpers do not enforce timestamp freshness, store nonces, or bind HTTP method,
path, or query. Replay prevention is an application policy. Outbound API requests
use `Authorization`; incoming callbacks use the four `Sud-*` headers above.

## License
This project is licensed under the MIT License. See the LICENSE file for details.