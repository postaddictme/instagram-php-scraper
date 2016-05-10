# instagram-php-scraper
# Usage

`composer require raiym/instagram-php-scraper`


```php
use InstagramScraper\Instagram;

$instagram = new Instagram();
$account = $instagram->getAccount('kevin');
/*
Available properties: 
    $username;
    $followsCount;
    $followedByCount;
    $profilePicUrl;
    $id;
    $biography;
    $fullName;
    $mediaCount;
    $isPrivate;
    $externalUrl;
*/
echo $account->followedByCount;

$medias = $instagram->getMedias('kevin', 150);

/*
Available properties: 
    $id;
    $createdTime;
    $type;
    $link;
    $imageLowResolutionUrl;
    $imageThumbnailUrl;
    $imageStandardResolutionUrl;
    $imageHighResolutionUrl;
    $caption;
    $videoLowResolutionUrl;
    $videoStandardResolutionUrl;
    $videoLowBandwidthUrl;
*/
echo $medias[0]->imageHighResolutionUrl;
echo $medias[0]->caption;

$media = $instagram->getMediaByCode('BDs9iwfL7XA');

$media = $instagram->getMediaByUrl('https://www.instagram.com/p/BDs9iwfL7XA/');
echo $instagram->owner->username;

```

