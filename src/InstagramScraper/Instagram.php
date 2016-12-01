<?php

namespace InstagramScraper;

use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Model\Account;
use InstagramScraper\Model\Comment;
use InstagramScraper\Model\Location;
use InstagramScraper\Model\Media;
use InstagramScraper\Model\Tag;
use Unirest\Request;

class Instagram
{
    const MAX_COMMENTS_PER_REQUEST = 300;

    public static function getAccount($username)
    {
        $response = Request::get(Endpoints::getAccountJsonLink($username));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        $userArray = json_decode($response->raw_body, true);
        if (!isset($userArray['user'])) {
            throw new InstagramException('Account with this username does not exist');
        }
        return Account::fromAccountPage($userArray['user']);
    }

    public static function getAccountById($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('User id must be integer or integer wrapped in string');
        }

        $parameters = Endpoints::getAccountJsonInfoLinkByAccountId($id);
//        self::getContentsFromUrl($parameters);

//        $response = Request::get(Endpoints::getAccountJsonInfoLinkByAccountId($id));
//        if ($response->code === 404) {
//            throw new InstagramNotFoundException('Account with given username does not exist.');
//        }
//        if ($response->code !== 200) {
//            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
//        }
        $userArray = json_decode(self::getContentsFromUrl($parameters), true);
        if ($userArray['status'] === 'fail') {
            throw new InstagramException($userArray['message']);
        }
        if (!isset($userArray['username'])) {
            throw new InstagramNotFoundException('User with this id not found');
        }
        return Account::fromAccountPage($userArray);
    }

    private static function getContentsFromUrl($parameters)
    {
        if (!function_exists('curl_init')) {
            return false;
        }
        $random = self::generateRandomString();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Endpoints::INSTAGRAM_QUERY_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'q=' . $parameters);
        $headers = array();
        $headers[] = "Cookie:  csrftoken=$random;";
        $headers[] = "X-Csrftoken: $random";
        $headers[] = "Referer: https://www.instagram.com/";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getMedias($username, $count = 20, $maxId = '')
    {
        $index = 0;
        $medias = [];
        $isMoreAvailable = true;
        while ($index < $count && $isMoreAvailable) {
            $response = Request::get(Endpoints::getAccountMediasJsonLink($username, $maxId));
            if ($response->code !== 200) {
                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
            }

            $arr = json_decode($response->raw_body, true);
            if (!is_array($arr)) {
                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
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
            if (count($arr['items']) == 0) {
                return $medias;
            }
            $maxId = $arr['items'][count($arr['items']) - 1]['id'];
            $isMoreAvailable = $arr['more_available'];
        }
        return $medias;
    }

    public static function getPaginateMedias($username, $maxId = '')
    {
        $hasNextPage = true;
        $medias = [];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage
        ];

        $response = Request::get(Endpoints::getAccountMediasJsonLink($username, $maxId));

        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        $arr = json_decode($response->raw_body, true);

        if (!is_array($arr)) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        if (count($arr['items']) === 0) {
            return $toReturn;
        }

        foreach ($arr['items'] as $mediaArray) {
            $medias[] = Media::fromApi($mediaArray);
        }

        $maxId = $arr['items'][count($arr['items']) - 1]['id'];
        $hasNextPage = $arr['more_available'];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage
        ];

        return $toReturn;
    }

    public static function getMediaByCode($mediaCode)
    {
        return self::getMediaByUrl(Endpoints::getMediaPageLink($mediaCode));
    }

    public static function getMediaByUrl($mediaUrl)
    {
        if (filter_var($mediaUrl, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Malformed media url');
        }
        $response = Request::get(rtrim($mediaUrl, '/') . '/?__a=1');
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Media with given code does not exist or account is private.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $mediaArray = json_decode($response->raw_body, true);
        if (!isset($mediaArray['media'])) {
            throw new InstagramException('Media with this code does not exist');
        }
        return Media::fromMediaPage($mediaArray['media']);
    }

    public static function getMediasByTag($tag, $count = 12, $maxId = '')
    {
        $index = 0;
        $medias = [];
        $hasNextPage = true;
        while ($index < $count && $hasNextPage) {
            $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId));
            if ($response->code !== 200) {
                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
            }

            $arr = json_decode($response->raw_body, true);
            if (!is_array($arr)) {
                throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
            }
            if (count($arr['tag']['media']['count']) === 0) {
                return [];
            }
            $nodes = $arr['tag']['media']['nodes'];
            foreach ($nodes as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                $medias[] = Media::fromTagPage($mediaArray);
                $index++;
            }
            if (count($nodes) == 0) {
                return $medias;
            }
            $maxId = $arr['tag']['media']['page_info']['end_cursor'];
            $hasNextPage = $arr['tag']['media']['page_info']['has_next_page'];
        }
        return $medias;
    }

    public static function getPaginateMediasByTag($tag, $maxId = '')
    {
        $hasNextPage = true;
        $medias = [];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage
        ];

        $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId));

        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        $arr = json_decode($response->raw_body, true);

        if (!is_array($arr)) {
            throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
        }

        if (count($arr['tag']['media']['count']) === 0) {
            return $toReturn;
        }

        $nodes = $arr['tag']['media']['nodes'];

        if (count($nodes) == 0) {
            return $toReturn;
        }

        foreach ($nodes as $mediaArray) {
            $medias[] = Media::fromTagPage($mediaArray);
        }

        $maxId = $arr['tag']['media']['page_info']['end_cursor'];
        $hasNextPage = $arr['tag']['media']['page_info']['has_next_page'];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage
        ];

        return $toReturn;
    }

    public static function searchAccountsByUsername($username)
    {
        $response = Request::get(Endpoints::getGeneralSearchJsonLink($username));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        $jsonResponse = json_decode($response->raw_body, true);
        if (!isset($jsonResponse['status']) || $jsonResponse['status'] != 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        if (!isset($jsonResponse['users']) || count($jsonResponse['users']) == 0) {
            return [];
        }

        $accounts = [];
        foreach ($jsonResponse['users'] as $jsonAccount) {
            $accounts[] = Account::fromSearchPage($jsonAccount['user']);
        }
        return $accounts;
    }

    public static function searchTagsByTagName($tag)
    {
        $response = Request::get(Endpoints::getGeneralSearchJsonLink($tag));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        $jsonResponse = json_decode($response->raw_body, true);
        if (!isset($jsonResponse['status']) || $jsonResponse['status'] != 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }

        if (!isset($jsonResponse['hashtags']) || count($jsonResponse['hashtags']) == 0) {
            return [];
        }
        $hashtags = [];
        foreach ($jsonResponse['hashtags'] as $jsonHashtag) {
            $hashtags[] = Tag::fromSearchPage($jsonHashtag['hashtag']);
        }
        return $hashtags;
    }

    public static function getTopMediasByTagName($tagName)
    {
        $response = Request::get(Endpoints::getMediasJsonByTagLink($tagName, ''));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $jsonResponse = json_decode($response->raw_body, true);
        $medias = [];
        foreach ($jsonResponse['tag']['top_posts']['nodes'] as $mediaArray) {
            $medias[] = Media::fromTagPage($mediaArray);
        }
        return $medias;
    }

    public static function getMediaById($mediaId)
    {
        $mediaLink = Media::getLinkFromId($mediaId);
        return self::getMediaByUrl($mediaLink);
    }

    public static function getMediaCommentsById($mediaId, $count = 10, $maxId = null)
    {
        $code = Media::getCodeFromId($mediaId);
        return self::getMediaCommentsByCode($code, $count, $maxId);
    }

    public static function getMediaCommentsByCode($code, $count = 10, $maxId = null)
    {
        $remain = $count;
        $comments = [];
        $index = 0;
        $hasPrevious = true;
        while ($hasPrevious && $index < $count) {
            if ($remain > self::MAX_COMMENTS_PER_REQUEST) {
                $numberOfCommentsToRetreive = self::MAX_COMMENTS_PER_REQUEST;
                $remain -= self::MAX_COMMENTS_PER_REQUEST;
                $index += self::MAX_COMMENTS_PER_REQUEST;
            } else {
                $numberOfCommentsToRetreive = $remain;
                $index += $remain;
                $remain = 0;
            }
            if (!isset($maxId)) {
//                $response = Request::get(Endpoints::getLastCommentsByCodeLink($code, $numberOfCommentsToRetreive));
                $parameters = Endpoints::getLastCommentsByCodeLink($code, $numberOfCommentsToRetreive);

            } else {
//                $response = Request::get(Endpoints::getCommentsBeforeCommentIdByCode($code, $numberOfCommentsToRetreive, $maxId));
                $parameters = Endpoints::getCommentsBeforeCommentIdByCode($code, $numberOfCommentsToRetreive, $maxId);
            }
//            if ($response->code === 404) {
//                throw new InstagramNotFoundException('Account with given username does not exist.');
//            }
//            if ($response->code !== 200) {
//                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
//            }
            $jsonResponse = json_decode(self::getContentsFromUrl($parameters), true);
            $nodes = $jsonResponse['comments']['nodes'];
            foreach ($nodes as $commentArray) {
                $comments[] = Comment::fromApi($commentArray);
            }
            $hasPrevious = $jsonResponse['comments']['page_info']['has_previous_page'];
            $numberOfComments = $jsonResponse['comments']['count'];
            if ($count > $numberOfComments) {
                $count = $numberOfComments;
            }
            if (sizeof($nodes) == 0) {
                return $comments;
            }
            $maxId = $nodes[sizeof($nodes) - 1]['id'];
        }
        return $comments;
    }

    public static function getLocationTopMediasById($facebookLocationId)
    {
        $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Location with this id doesn\'t exist');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $jsonResponse = json_decode($response->raw_body, true);
        $nodes = $jsonResponse['location']['top_posts']['nodes'];
        $medias = [];
        foreach ($nodes as $mediaArray) {
            $medias[] = Media::fromTagPage($mediaArray);
        }
        return $medias;
    }

    public static function getLocationMediasById($facebookLocationId, $quantity = 12, $offset = '')
    {
        $index = 0;
        $medias = [];
        $hasNext = true;
        while ($index < $quantity && $hasNext) {
            $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId, $offset));
            if ($response->code !== 200) {
                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
            }
            $arr = json_decode($response->raw_body, true);
            $nodes = $arr['location']['media']['nodes'];
            foreach ($nodes as $mediaArray) {
                if ($index === $quantity) {
                    return $medias;
                }
                $medias[] = Media::fromTagPage($mediaArray);
                $index++;
            }
            if (count($nodes) == 0) {
                return $medias;
            }
            $hasNext = $arr['location']['media']['page_info']['has_next_page'];
            $offset = $arr['location']['media']['page_info']['end_cursor'];
        }
        return $medias;
    }

    public static function getLocationById($facebookLocationId)
    {
        $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Location with this id doesn\'t exist');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $jsonResponse = json_decode($response->raw_body, true);
        return Location::makeLocation($jsonResponse['location']);
    }

    public static function getLastLikesByCode($code)
    {
        $response = Request::get(Endpoints::getLastLikesByCodeLink($code));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Media with this shortcode doesn\'t exist');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $jsonResponse = json_decode($response->raw_body, true);
        $users = [];
        foreach ($jsonResponse['likes']['nodes'] as $userArray) {
            $users[] = Account::fromAccountPage($userArray['user']);
        }
        return $users;
    }
}