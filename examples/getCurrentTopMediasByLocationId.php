<?php
ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'user', 'passwd', new Psr16Adapter('Files'));
$instagram->login();

$medias = $instagram->getCurrentTopMediasByLocationId('116231');
$media = $medias[0];


$seperator = PHP_SAPI === 'cli' ? "\n" : "<br>\n";
echo "Media info:$seperator";
echo "Id: {$media->getId()}$seperator";
echo "Shortcode: {$media->getShortCode()}$seperator";
echo "Created at: {$media->getCreatedTime()}$seperator";
echo "Caption: {$media->getCaption()}$seperator";
echo "Number of comments: {$media->getCommentsCount()}";
echo "Number of likes: {$media->getLikesCount()}";
echo "Get link: {$media->getLink()}";
echo "High resolution image: {$media->getImageHighResolutionUrl()}";
echo "Media type (video or image): {$media->getType()}";
$account = $media->getOwner();
echo "Account info:$seperator";
echo "Id: {$account->getId()}$seperator";
echo "Username: {$account->getUsername()}$seperator";
echo "Full name: {$account->getFullName()}$seperator";
echo "Profile pic url: {$account->getProfilePicUrl()}$seperator";

echo "<br>";
echo "Location Name: {$media->getLocationName()}$seperator";
echo "Location Slug: {$media->getLocationSlug()}$seperator";
