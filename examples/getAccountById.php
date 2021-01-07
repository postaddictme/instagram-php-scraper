<?php
require __DIR__ . '/../vendor/autoload.php';

$account = (new \InstagramScraper\Instagram(new \GuzzleHttp\Client()))->getAccountById('3');

// Available fields
echo "Account info:\n";
echo "Id: {$account->getId()}\n";
echo "Username: {$account->getUsername()}\n";
echo "Full name: {$account->getFullName()}\n";
echo "Biography: {$account->getBiography()}\n";
echo "Profile picture url: {$account->getProfilePicUrl()}\n";
echo "External link: {$account->getExternalUrl()}\n";
echo "Number of published posts: {$account->getMediaCount()}\n";
echo "Number of followers: {$account->getFollowedByCount()}\n";
echo "Number of follows: {$account->getFollowsCount()}\n";
echo "Is private: {$account->isPrivate()}\n";
echo "Is verified: {$account->isVerified()}\n";