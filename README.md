# instagram-php-scraper
# Usage

`composer require raiym/instagram-php-scraper`


```php
use InstagramScraper\Instagram;

```

### Get account info
```php
$account = Instagram::getAccount('kevin');
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
### Get account info by userId
```php
$account = Instagram::getAccountById(193886659);
echo $account->username;
```

### Search users by username
```php
$medias = Instagram::searchAccountsByUsername('durov');
echo '<pre>';
echo json_encode($medias);
echo '</pre><br/>';
```

### Get account medias
```php
$medias = Instagram::getMedias('kevin', 150);

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
$media = Instagram::getMediaByCode('BDs9iwfL7XA');
```

### Get media by url
```php
$media = Instagram::getMediaByUrl('https://www.instagram.com/p/BDs9iwfL7XA/');
echo $media->owner->username;
```

### Get media by id
```php
$media = Instagram::getMediaById(1042815830884781756);
```

### Search medias by tag name
```php
$medias = Instagram::getMediasByTag('zara', 30);
echo json_encode($medias);
```

### Get top medias by tag name
```php
$medias = Instagram::getTopMediasByTagName('durov');
```

### Get media by id
```php
$media = Instagram::getMediaById(1270593720437182847)
```

### Convert media id to shortcode
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

### Get media comments by shortcode
```php
$comments = Instagram::getMediaCommentsByCode('BG3Iz-No1IZ', 8000);
```

### Get media comments by id
```php
$comments = Instagram::getMediaCommentsById('1130748710921700586', 10000)
```

### Get location id
```php
$medias = Instagram::getLocationById(1);
```

### Get location top medias by location id
```php
$medias = Instagram::getLocationTopMediasById(1);
```

### Get location medias by location id
```php
$medias = Instagram::getLocationMediasById(1);
```