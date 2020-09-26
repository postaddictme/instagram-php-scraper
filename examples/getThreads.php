<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', new Psr16Adapter('Files'));
$instagram->login();
$instagram->saveSession();

$threads = $instagram->getThreads(10, 10, 20);
$thread = $threads[0];

echo "Thread Info:\n";
echo "Id: {$thread->getId()}\n";
echo "Title: {$thread->getTitle()}\n";
echo "Type: {$thread->getType()}\n";
echo "Read State: {$thread->getReadState()}\n\n";

$items = $thread->getItems();
$item = $items[0];

echo "Item Info:\n";
echo "Id: {$item->getId()}\n";
echo "Type: {$item->getType()}\n";
echo "Time: {$item->getTime()}\n";
echo "User ID: {$item->getUserId()}\n";
echo "Text: {$item->getText()}\n\n";

$reelShare = $item->getReelShare();

echo "Reel Share Info:\n";
echo "Text: {$reelShare->getText()}\n";
echo "Type: {$reelShare->getType()}\n";
echo "Owner Id: {$reelShare->getOwnerId()}\n";
echo "Mentioned Id: {$reelShare->getMentionedId()}\n\n";

$reelMedia = $reelShare->getMedia();

echo "Reel Media Info:\n";
echo "Id: {$reelMedia->getId()}\n";
echo "Caption: {$reelMedia->getCaption()}\n";
echo "Code: {$reelMedia->getCode()}\n";
echo "Expiring At: {$reelMedia->getExpiringAt()}\n";
echo "Image: {$reelMedia->getImage()}\n\n";

// $user = $reelMedia->getUser(); // InstagramScraper\Model\Account
// $mentions = $reelMedia->getMentions(); // InstagramScraper\Model\Account[]
