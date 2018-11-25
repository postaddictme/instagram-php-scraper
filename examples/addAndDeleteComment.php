<?php
use InstagramScraper\Exception\InstagramException;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', '/path/to/cache/folder');
$instagram->login();

try {
    // add comment to post
    $mediaId = '1663256735663694497';
    $comment = $instagram->addComment($mediaId, 'Text 1');
    // replied to comment
    $instagram->addComment($mediaId, 'Text 2', $comment);
    
    $instagram->deleteComment($mediaId, $comment);
} catch (InstagramException $ex) {
    echo $ex->getMessage();
}
