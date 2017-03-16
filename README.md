# instagram-php-scraper
# Usage

#v0.5.0
Important update: 

First of all thank you guys for your suggestions and support. 
This is *not final* fix for getting medias by tags and locations. 

if you need to get medias by tags or locations:
```php
$instagram = Instagram::withCredentials('username', 'password');
$instagram->login();
// And then you will be able to query instagram with newly updated methods. (Notice that these methods are not static anymore)

$user = $instagram->getAccountById(3);
$medias $instagram->getLocationTopMediasById(1)
$medias = $instagram->getLocationMediasById(1);
$location $instagram->getLocationById(1);
$medias = $instagram->getTopMediasByTagName('hello');
```

Be carefull with login method. I am planning to implement session caching soon
 

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
$users = Instagram::searchAccountsByUsername('durov');
echo '<pre>';
echo json_encode($users);
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
    $captionIsEdited;
    $isAd;
    $videoLowResolutionUrl;
    $videoStandardResolutionUrl;
    $videoLowBandwidthUrl;
    $videoViews;
    $code;
    $owner;
    $ownerId;
    $likesCount;
    $locationId;
    $locationName;
    $commentsCount;
    
*/
echo $medias[0]->imageHighResolutionUrl;
echo $medias[0]->caption;

```

### Paginate medias
```php
$result = Instagram::getPaginateMedias('kevin');
$medias = $result['medias']

if($result['hasNextPage'] === true) {
    $result = Instagram::getPaginateMedias('kevin', $result['maxId']);
    $medias = array_merge($medias, $result['medias']);
}

echo json_encode($medias);
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

### Paginate medias by tag name
```php
$result = Instagram::getPaginateMediasByTag('zara');
$medias = $result['medias']

if($result['hasNextPage'] === true) {
    $result = Instagram::getPaginateMediasByTag('zara', $result['maxId']);
    $medias = array_merge($medias, $result['medias']);
}

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

### Other
Java library: https://github.com/postaddictme/instagram-java-scraper