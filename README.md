# instagram-php-scraper
# Usage

`composer require raiym/instagram-php-scraper`


```php
use InstagramScraper\InstagramScraper;

$instagram = new InstagramScraper();
$account = $instagram->getAccount('kevin');
echo $account->followedByCount;
echo $account->mediaCount;

$medias = $instagram->getMedias('kevin', 150);

echo $medias[0]->imageStandardResolutionUrl;
echo $medias[0]->caption;
```