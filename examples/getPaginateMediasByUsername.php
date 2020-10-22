<?php
require __DIR__ . '/../vendor/autoload.php';

$instagram = new \InstagramScraper\Instagram(new \GuzzleHttp\Client());
$response = $instagram->getPaginateMedias('kevin');

foreach ($response['medias'] as $media) {
    /** @var \InstagramScraper\Model\Media $media */

    echo "Media info:" . PHP_EOL;
    echo "Id: {$media->getId()}" . PHP_EOL;
    echo "Shortcode: {$media->getShortCode()}" . PHP_EOL;
    echo "Created at: {$media->getCreatedTime()}" . PHP_EOL;
    echo "Caption: {$media->getCaption()}" . PHP_EOL;
    echo "Number of comments: {$media->getCommentsCount()}" . PHP_EOL;
    echo "Number of likes: {$media->getLikesCount()}" . PHP_EOL;
    echo "Get link: {$media->getLink()}" . PHP_EOL;
    echo "High resolution image: {$media->getImageHighResolutionUrl()}" . PHP_EOL;
    echo "Media type (video or image): {$media->getType()}" . PHP_EOL . PHP_EOL;
    $account = $media->getOwner();

    echo "Account info:" . PHP_EOL;
    echo "Id: {$account->getId()}" . PHP_EOL;
    echo "Username: {$account->getUsername()}" . PHP_EOL;
    echo "Full name: {$account->getFullName()}" . PHP_EOL;
    echo "Profile pic url: {$account->getProfilePicUrl()}" . PHP_EOL;
    echo  PHP_EOL  . PHP_EOL;
}

echo "HasNextPage: {$response['hasNextPage']}" . PHP_EOL;
echo "MaxId: {$response['maxId']}" . PHP_EOL;