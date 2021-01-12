<?php
ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);
$seperator = PHP_SAPI === 'cli' ? "\n" : "\n<br>";

if (PHP_SAPI === 'cli') {
  $seperator = "\n";
  $c1 = '';
  $c2 = '';
  $c3 = '';
  $cend = '';
}
else {
  $seperator = "\n";
  $c1 = '<label style="color:red;">';
  $c2 = '<label style="color:green;">';
  $c3 = '<label style="color:blue;">';
  $cend = '</label>';
}
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$seperator = PHP_SAPI === 'cli' ? "\n" : "<br>\n";
$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'ricdijk', '@leusdeN2020', new Psr16Adapter('Files'));
$instagram->login();
$instagram->saveSession(3600*24);


// getFeed has 4 arguments:
// - Nr of medias
// - maxId (cursor)
// - nr of comments
// - nr of likes => dows not seem to do anything
$medias = $instagram->getFeed(50,'',6,4);
echo "${c1}Test getFeed():${cend}";
display_media($medias);
echo "${seperator}${seperator}";


echo "${seperator}==========================================================================================";
echo "${seperator}${c1}Test getPaginateFeed():${cend}";
$maxId       = null;
$hasNextPage = true;
$count_fetch = 0;
while ($hasNextPage)
{
  $count_fetch++;
  if ($count_fetch>3) break;

  $arr = $instagram->getPaginateFeed(10, $maxId, 6, 4);
  $maxId            = $arr['maxId'];
  $hasNextPage      = $arr['hasNextPage'];

  echo "${seperator}+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++";
  echo "${seperator}${c2}Fetch page: " . $count_fetch."${cend}";
  echo "${seperator}";
  echo "${seperator}Media Count: " . $arr['media_count'];
  echo ", Storie Count: " . $arr['storie_count'];
  display_media($arr['medias']);
  echo "${seperator}................................................................................";
  display_story($arr['userStories']);
  echo "${seperator}";

}

function display_media($medias)
{
  global $seperator, $c1, $c2, $c3, $cend;
  echo "${seperator}Nr of ${c3}media${cend} page: ".count($medias);
  echo "${seperator}First media Id     :  " . $medias[0]->getId();
  echo "${seperator}First media Creates:  " . $medias[0]->getCreatedTime();
  echo "${seperator}First media Caption:  " . $medias[0]->getCaption();

  if (PHP_SAPI === 'cli') echo "${seperator}First media image:  " . $medias[0]->getImageHighResolutionUrl();
  else echo "${seperator}<img height=100 src='" . $medias[0]->getImageHighResolutionUrl() . "'>\n";
}

function display_story($userStories)
{
  global $seperator, $c1, $c2, $c3, $cend;
  echo "${seperator}Nr of ${c3}userStories${cend} page: ".count($userStories);
  if (count($userStories)>0)
  {
    echo "${seperator}First userStory expiringAt     :  " . gmdate("Y-m-d H:i:s", $userStories[0]->getExpiringAt());
    echo " - " . floor(($userStories[0]->getExpiringAt()-time())/3600) . "h";
    echo " " . floor((($userStories[0]->getExpiringAt()-time())%3600)/60) . "m ";
    echo "${seperator}First userStory last mutation:  " . gmdate("Y-m-d H:i:s", $userStories[0]->getLastMutatedAt());
    echo " - " . floor(($userStories[0]->getLastMutatedAt()-time())/3600) . "h";

    echo "${seperator}First userStory user Id:  " . $userStories[0]->getOwner()->getId();
    echo "${seperator}First userStory user Name:  " . $userStories[0]->getOwner()->getUsername();
    echo "${seperator}${seperator}Note: no content of story, only the fact that a story of this user is present";
    echo "${seperator}Fetch storie(s) with command:";
    echo " '\$Instagram->getStories(".$userStories[0]->getOwner()->getId().");'";
  }
}
