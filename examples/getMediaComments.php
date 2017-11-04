<?php
require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', 'path/to/cache/folder');
$instagram->login();

// Get media comments by shortcode
$comments = $instagram->getMediaCommentsByCode('BG3Iz-No1IZ', 8000);

// or by id
$comments = $instagram->getMediaCommentsById('1130748710921700586', 10000);

// Let's take first comment in array and explore available fields
$comment = $comments[0];

echo "Comment info: \n";
echo "Id: {$comment->getId()}\n";
echo "Created at: {$comment->getCreatedAt()}\n";
echo "Comment text: {$comment->getText()}\n";
$account = $comment->getOwner();
echo "Comment owner: \n";
echo "Id: {$account->getId()}";
echo "Username: {$account->getUsername()}";
echo "Profile picture url: {$account->getProfilePicUrl()}\n";

// You can start loading comments from specific comment by providing comment id
$comments = $instagram->getMediaCommentsByCode('BG3Iz-No1IZ', 200, $comment->getId());

// ...



