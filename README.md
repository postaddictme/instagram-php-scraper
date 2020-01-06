# Instagram PHP Scraper
This library is based on the Instagram web version. We develop it because nowadays it is hard to get an approved Instagram application. The purpose is to support every feature that the web desktop and mobile version support. 

## Dependencies

- [PSR-16](http://www.php-fig.org/psr/psr-16/)


## Code Example
```php
$instagram = Instagram::withCredentials('username', 'password');
$instagram->login();
$account = $instagram->getAccountById(3);
echo $account->getUsername();
```

Some methods do not require authentication: 
```php
$instagram = new Instagram();
$nonPrivateAccountMedias = $instagram->getMedias('kevin');
echo $nonPrivateAccountMedias[0]->getLink();
```

If you use authentication it is recommended to cache the user session. In this case you don't need to run the `$instagram->login()` method every time your program runs:

```php
use Phpfastcache\Helper\Psr16Adapter;

$instagram = Instagram::withCredentials('username', 'password', new Psr16Adapter('Files'));
$instagram->login(); // will use cached session if you can force login $instagram->login(true)
$account = $instagram->getAccountById(3);
echo $account->getUsername();
```

Using proxy for requests:

```php
$instagram = new Instagram();
Instagram::setProxy([
    'address' => '111.112.113.114',
    'port'    => '8080',
    'tunnel'  => true,
    'timeout' => 30,
]);
// Request with proxy
$account = $instagram->getAccount('kevin');
Instagram::disableProxy();
// Request without proxy
$account = $instagram->getAccount('kevin');
```

Using a list of fallbacks proxies for requests :

```php
Instagram::setProxies([
    '', // Empty or null can be set : no proxy will be used when selected
    'tcp://127.0.0.1:123',
    'http://foo:bar@127.0.0.1:456',
]);
```


Auto Retry Failed Requests (for connections errors, 4xx and 5xx) : 

```php
Instagram::setAutoRetry(true); // default: false
Instagram::setMaxNbRetries(3); // default: 3
```

## Installation

### Using composer

```sh
composer.phar require raiym/instagram-php-scraper
```
or 
```sh
composer require raiym/instagram-php-scraper
```

### If you don't have composer
You can download it [here](https://getcomposer.org/download/).

## Examples
See examples [here](https://github.com/postaddictme/instagram-php-scraper/tree/master/examples).

## Other
Java library: https://github.com/postaddictme/instagram-java-scraper
