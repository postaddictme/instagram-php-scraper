# instagram-php-scraper
# Usage

`composer require raiym/instagram-php-scraper`


```php
use InstagramScraper\Instagram;

$instagram = new Instagram();
```

### Get account info
```php
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
```

### Search users by username
```php
$medias = $instagram->searchAccountsByUsername('durov');
echo '<pre>';
echo json_encode($medias);
echo '</pre><br/>';
```

### Get account medias
```php
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

```

### Get media by code
```php
$media = $instagram->getMediaByCode('BDs9iwfL7XA');
```

### Get media by url
```php
$media = $instagram->getMediaByUrl('https://www.instagram.com/p/BDs9iwfL7XA/');
echo $media->owner->username;
```

### Get media by id
```php
$media = $instagram->getMediaById(1042815830884781756);
```

### Search medias by tag name
```php
$medias = $instagram->getMediasByTag('zara', 30);
echo json_encode($medias);
```

### Get top medias by tag name
```php
$medias = $instagram->getTopMediasByTagName('durov');
```

### Convert media it to shortcode
```php
echo 'CODE: ' . Media::getCodeFromId('1270593720437182847_3');
// OR
echo 'CODE: ' . Media::getCodeFromId('1270593720437182847');
// OR
echo 'CODE: ' . Media::getCodeFromId(1270593720437182847);
// CODE: BGiDkHAgBF_
// So you can do like this: instagram.com/p/BGiDkHAgBF_
```

### Convert shortcode to media id
```php
echo 'Media id: ' . Media::getIdFromCode('BGiDkHAgBF_');
// Media id: 1270593720437182847
```