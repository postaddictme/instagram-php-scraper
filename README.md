# Instagram PHP Scraper
This library is based on the Instagram web version. We develop it because nowadays it is hard to get an approved Instagram application. The purpose is to support every feature that the web desktop and mobile version support. 

## Dependencies
- PHP >= 7.2
- [PSR-16](http://www.php-fig.org/psr/psr-16/)
- [PSR-18](http://www.php-fig.org/psr/psr-18/)


## Code Example
```php
$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password');
$instagram->login();
$account = $instagram->getAccountById(3);
echo $account->getUsername();
```

Some methods do not require authentication: 
```php
$instagram = new \InstagramScraper\Instagram(new \GuzzleHttp\Client());
$nonPrivateAccountMedias = $instagram->getMedias('kevin');
echo $nonPrivateAccountMedias[0]->getLink();
```

If you use authentication it is recommended to cache the user session. In this case you don't need to run the `$instagram->login()` method every time your program runs:

```php
use Phpfastcache\Helper\Psr16Adapter;

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login(); // will use cached session if you want to force login $instagram->login(true)
$instagram->saveSession();  //DO NOT forget this in order to save the session, otherwise have no sense
$account = $instagram->getAccountById(3);
echo $account->getUsername();
```

Using proxy for requests:

```php
// https://docs.guzzlephp.org/en/stable/request-options.html#proxy
$instagram = new \InstagramScraper\Instagram(new \GuzzleHttp\Client(['proxy' => 'tcp://localhost:8125']));
// Request with proxy
$account = $instagram->getAccount('kevin');
\InstagramScraper\Instagram::setHttpClient(new \GuzzleHttp\Client());
// Request without proxy
$account = $instagram->getAccount('kevin');
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
