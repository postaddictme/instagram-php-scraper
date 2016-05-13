<?php

namespace InstagramScraper;

use InvalidArgumentException;
use Unirest\Request;

class Instagram implements InstagramDataProvider
{
    const INSTAGRAM_URL = 'https://www.instagram.com/';

    function getAccount($username)
    {
        $response = Request::get(self::INSTAGRAM_URL . $username);
        if ($response->code === 404) {
            throw new InstagramException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        $json = self::getJsonPayload($response->body);
        $userArray = json_decode($json, true);
        if (!is_array($userArray)) {
            throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
        }
        if (!isset($userArray['entry_data']['ProfilePage'])) {
            throw new InstagramException('Account with this username does not exist');
        }
        return Account::fromAccountPage($userArray['entry_data']['ProfilePage'][0]['user']);
    }

    private static function getJsonPayload($pageString)
    {
        $arr = explode('window._sharedData = ', $pageString);
        $json = explode(';</script>', $arr[1])[0];
        return $json;
    }

    function getMedias($username, $count = 20)
    {
        $index = 0;
        $medias = [];
        $maxId = '';
        $isMoreAvailable = true;
        while ($index < $count && $isMoreAvailable) {
            $response = Request::get(self::INSTAGRAM_URL . $username . '/media/?max_id=' . $maxId);
            if ($response->code !== 200) {
                throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
            }

            $arr = json_decode($response->raw_body, true);
            if (!is_array($arr)) {
                throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
            }
            if (count($arr['items']) === 0) {
                return [];
            }
            foreach ($arr['items'] as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                $medias[] = Media::fromApi($mediaArray);
                $index++;
            }
            $maxId = $arr['items'][count($arr['items']) - 1]['id'];
            $isMoreAvailable = $arr['more_available'];
        }
        return $medias;
    }

    function getMediaByCode($mediaCode)
    {
        return self::getMediaByUrl(self::INSTAGRAM_URL . 'p/' . $mediaCode);
    }

    function getMediaByUrl($mediaUrl)
    {
        if (filter_var($mediaUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Malformed media url');

        }
        $response = Request::get($mediaUrl);
        if ($response->code === 404) {
            throw new InstagramException('Media with given code does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        $json = self::getJsonPayload($response->body);
        $mediaArray = json_decode($json, true);
        if (!isset($mediaArray['entry_data']['PostPage'])) {
            throw new InstagramException('Media with this code does not exist');
        }
        return Media::fromMediaPage($mediaArray['entry_data']['PostPage'][0]['media']);
    }
}