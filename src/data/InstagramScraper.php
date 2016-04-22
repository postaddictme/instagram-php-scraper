<?php

/**
 * Created by PhpStorm.
 * User: rajymbekkapisev
 * Date: 20/04/16
 * Time: 01:17
 */
class InstagramScraper implements InstagramDataProvider
{
    const INSTAGRAM_URL = 'https://www.instagram.com/';

    function getAccount($username)
    {
        $response = Unirest\Request::get(self::INSTAGRAM_URL . $username);
        if ($response->code !== 200) {
            return null;
        }
        $arr = explode('window._sharedData = ', $response->body);
        $json = explode(';</script>', $arr[1])[0];
        $userArray = json_decode($json, true);
        return new Account($userArray['entry_data']['ProfilePage'][0]['user']);
    }

    function getMedias($username, $count = 20)
    {
        $index = 0;
        $medias = [];
        $maxId = '';
        $isMoreAvailable = true;
        while ($index < $count && $isMoreAvailable) {
            $response = Unirest\Request::get(self::INSTAGRAM_URL . $username . '/media/?max_id=' . $maxId);
            if ($response->code !== 200) {
                return [];
            }

            $arr = json_decode($response->raw_body, true);
            if (count($arr['items']) === 0) {
                return [];
            }
            foreach ($arr['items'] as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                array_push($medias, new Media($mediaArray));
                $index++;
            }
            $maxId = $arr['items'][count($arr['items']) - 1]['id'];
            $isMoreAvailable = $arr['more_available'];
        }
        return $medias;
    }
}