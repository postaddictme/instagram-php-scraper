<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'passwd', new Psr16Adapter('Files'));
$instagram->login();

//$userId = $instagram->getAccount('instagram')->getId();
$userId = 556625332;
echo "<pre>";

$highlights = $instagram->getHighlights($userId);
$hcount=0;
foreach ($highlights as $highlight) {
    $hcount++;
    echo "\n------------------------------------------------------------------------------------------------------------------------\n";
    echo "Highlight info ($hcount):\n";
    echo "<img width=80 src='".$highlight->getImageThumbnailUrl()."'>\n";
    echo "Id: {$highlight->getId()}\n";
    echo "Title: {$highlight->getTitle()}\n";
    echo "Image thumbnail url: {$highlight->getImageThumbnailUrl()}\n";
    echo "Image cropped thumbnail url: {$highlight->getImageCroppedThumbnailUrl()}\n";
    echo "Owner Id: {$highlight->getOwnerId()}\n";
    $account = $highlight->getOwner();
    echo "Account info:\n";
    echo " Id: {$account->getId()}\n";
    echo " Username: {$account->getUsername()}\n";
    echo " Profile pic url: {$account->getProfilePicUrl()}\n";

    echo "------------------------------------------------------------------------------------------------------------------------\n";

    $userStories=$instagram->richd_test($highlight->getId());
    for ($i=0; $i<count($userStories);$i++)
    {
      $stories = $userStories[$i]->getStories();
      //$owner   = $userStories[$i]->getOwner();
      //echo "\n===========================================================";
      //echo "\nUserStorie: " . $i;
      //echo "\nId:         " . $owner['id'];
      //echo "\nUserName:   " . $owner['username'];

      //for each stories => get Story
      for ($j=0; $j<count($stories);$j++)
      {
          $story = $stories[$j];
          echo "\n--------------------------------------------------------";
          echo "\nStorie:         " . $j;
          echo "\nId:             " . $story['id'];
          echo "\nCreation Time:  " . $story['createdTime'];
          echo "\nType:           " . $story['type'];
          echo "\n<img height=100 src=\"".$story['imageThumbnailUrl']."\">";

      }
    }

}
echo "</pre>";
