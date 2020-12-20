<?php
ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
use Phpfastcache\Helper\Psr16Adapter;

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'ricdijk', 'Lacanau2018!', new Psr16Adapter('Files'));
$instagram->login();
$instagram->saveSession();


// *************************** get storie from unsetStories **********************************
/* getStories returns the following imap_fetchstructure (class Story extends Media, can be treated as Media, however no 'owner' in Stories -> owner in UserStories)
- Array
  - UserStories
    - Owner
    - Stories
      - Story
      - Story
      - Story
      - ....
  - UserStories
    - Owner
    - Stories
      - Story
      - Story
      - Story
      - ....
  - .....
*/
// Get userStories
$userStories = $instagram->getStories();

//For each userStorie => get Stories
for ($i=0; $i<count($userStories);$i++)
{
  $stories = $userStories[$i]->getStories();
  $owner   = $userStories[$i]->getOwner();
  echo "\n<br>===========================================================";
  echo "\n<br>UserStorie: " . $i;
  echo "\n<br>Id:         " . $owner['id'];
  echo "\n<br>UserName:   " . $owner['username'];

  //for each stories => get Story
  for ($j=0; $j<count($stories);$j++)
  {
      $story = $stories[$j];
      echo "\n<br>--------------------------------------------------------";
      echo "\n<br>Storie:         " . $j;
      echo "\n<br>Id:             " . $story['id'];
      echo "\n<br>Creation Time:  " . $story['createdTime'];
      echo "\n<br>Type:           " . $story['type'];
      echo "\n<br><img height=100 src=\"".$story['imageThumbnailUrl']."\">";

  }
}
?>
