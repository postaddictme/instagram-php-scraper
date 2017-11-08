# Instagram PHP Scrapper
This library based on Instagram web version. We develop it because nowadays it is hard to get approved Instagram application. 
The purpose support every feature that web desktop and mobile version support. 

## Code Example
```php
$instagram = Instagram::withCredentials('username', 'password');
$instagram->login();
$account = $instagram->getAccountById(3);
echo $account->getUsername();
```
Some methods does not require auth: 
```php
$instagram = new Instagram();
$nonPrivateAccountMedias = $instagram->getMedias('kevin');
echo $nonPrivateAccountMedias[0]->getLink();
```
If you use auth it is recommended to cash user session, in this case you don't need run `$instagram->login()` method every time your program runs:

```php
$instagram = Instagram::withCredentials('username', 'password', '/path/to/cache/folder/');
$instagram->login(); // will use cached session if you can force login $instagram->login(true)
$account = $instagram->getAccountById(3);
echo $account->getUsername();
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