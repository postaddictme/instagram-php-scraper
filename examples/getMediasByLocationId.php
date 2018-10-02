<?php
require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', '/path/to/cache/folder');
$instagram->login();

$medias = $instagram->getMediasByLocationId('1', 20);
$media = $medias[0];
echo "Media info:\n";
echo "Id: {$media->getId()}\n";
echo "Shortcode: {$media->getShortCode()}\n";
echo "Created at: {$media->getCreatedTime()}\n";
echo "Caption: {$media->getCaption()}\n";
echo "Number of comments: {$media->getCommentsCount()}";
echo "Number of likes: {$media->getLikesCount()}";
echo "Get link: {$media->getLink()}";
echo "High resolution image: {$media->getImageHighResolutionUrl()}";
echo "Media type (video or image): {$media->getType()}";
$account = $media->getOwner();
echo "Account info:\n";
echo "Id: {$account->getId()}\n";
echo "Username: {$account->getUsername()}\n";
echo "Full name: {$account->getFullName()}\n";
echo "Profile pic url: {$account->getProfilePicUrl()}\n";
