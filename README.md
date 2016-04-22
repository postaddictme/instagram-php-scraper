# instagram-php-scraper
# Usage

`composer require raiym/instagram-php-scraper`


```php
$instagram = new InstagramScraper();
$account = $instagram->getAccount('kevin');
echo $account->followedByCount;
echo $account->mediaCount;

// Returns last 150 medias
$medias = $instagram->getMedias('kevin', 150);

echo $medias[0]->imageStandardResolutionUrl;
echo $medias[0]->caption;

```