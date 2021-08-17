<?php
use Phpfastcache\Helper\Psr16Adapter;
require __DIR__ . '/../vendor/autoload.php';

function getStoryByUrl($instagram,$url)
{
		$username = explode("/",$url)[4];
		$id = explode("/",$url)[5];
		$account = $instagram->getAccount($username);
		$profile = $instagram->getStories([$account->getId()])[0];
		$stories = $profile->getStories();
		foreach ($stories as $story)
		{
			if ($story->getId() == $id) {
				return $story;
			}
		}
}


$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login();

$stories = getStoryByUrl($instagram,"https://www.instagram.com/stories/hossinasaadi/2642429697467772762/");
print_r($stories);
