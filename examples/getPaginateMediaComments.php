<?php
ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$seperator = PHP_SAPI === 'cli' ? "\n" : "<br>\n";
$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'user', 'passwd', new Psr16Adapter('Files'));
$instagram->login();


// You can start loading comments from specific comment by providing comment id
// $comments = $instagram->getPaginateMediaCommentsByCode('BG3Iz-No1IZ', 200, $comment->getId());
//
// However this results in a never ending loop of commentsCount
$mediaCode='CHiRTyDHgkc';
$comments = $instagram->getMediaCommentsByCode($mediaCode, 1000);
echo "Total nr of comments: ".count($comments);

$pageSize=floor(count($comments)/2)+2;
$comments = $instagram->getMediaCommentsByCode($mediaCode, $pageSize);
echo "${seperator}Nr of comments page 1: ".count($comments);
$comments = $instagram->getMediaCommentsByCode($mediaCode, $pageSize);
echo "${seperator}Nr of comments page 2: ".count($comments);
$comments = $instagram->getMediaCommentsByCode($mediaCode, $pageSize);
echo "${seperator}Nr of comments page 3: ".count($comments);
echo "${seperator}${seperator}Not working right: 3x page > total";
// ...


$maxId       = null;
$hasPrevious = true;
$counter     = 0;
while ($hasPrevious)
{
  $counter++;
  $PaginateComments = $instagram->getPaginateMediaCommentsByCode($mediaCode, $pageSize, $maxId);
  $comments         = $PaginateComments->comments;
  $maxId            = $PaginateComments->maxId;
  $hasPrevious      = $PaginateComments->hasPrevious;

  echo "${seperator}${seperator}--------------------------------------------------------------------------------";
  echo "${seperator}Nr of comments page ${counter}: ".count($comments);
  echo "${seperator}Total comments: " . $PaginateComments->commentsCount;
  echo "${seperator}Has Previous:   " . $PaginateComments->hasPrevious;

  echo "${seperator}First comment Id     :  " . $PaginateComments->comments[0]->getId();
  echo "${seperator}First comment Creates:  " . $PaginateComments->comments[0]->getCreatedAt();
  echo "${seperator}First comment Text   :  " . $PaginateComments->comments[0]->getText();

}
// ...
