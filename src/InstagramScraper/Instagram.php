<?php

namespace InstagramScraper;

use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Model\Account;
use InstagramScraper\Model\Comment;
use InstagramScraper\Model\Media;
use InstagramScraper\Model\Tag;
use Unirest\Request;

class Instagram
{
    public function getAccount($username)
    {
        $response = Request::get(Endpoints::getAccountJsonLink($username));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }

        $userArray = json_decode($response->raw_body, true);
        if (!isset($userArray['user'])) {
            throw new InstagramException('Account with this username does not exist');
        }
        return Account::fromAccountPage($userArray['user']);
    }

    public function getAccountById($id)
    {

        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('User id must be integer or integer wrapped in string');
        }
        $response = Request::get(Endpoints::getAccountJsonInfoLinkByAccountId($id));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        $userArray = json_decode($response->raw_body, true);
        if ($userArray['status'] === 'fail') {
            throw new InstagramException($userArray['message']);
        }
        if (!isset($userArray['username'])) {
            throw new InstagramNotFoundException('User with this id not found');
        }
        return Account::fromAccountPage($userArray);
    }

    public function getMedias($username, $count = 20)
    {
        $index = 0;
        $medias = [];
        $maxId = '';
        $isMoreAvailable = true;
        while ($index < $count && $isMoreAvailable) {
            $response = Request::get(Endpoints::getAccountMediasJsonLink($username, $maxId));
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
            if (count($arr['items']) == 0) {
                return $medias;
            }
            $maxId = $arr['items'][count($arr['items']) - 1]['id'];
            $isMoreAvailable = $arr['more_available'];
        }
        return $medias;
    }

    public function getMediaByCode($mediaCode)
    {
        return self::getMediaByUrl(Endpoints::getMediaPageLink($mediaCode));
    }

    public function getMediaByUrl($mediaUrl)
    {
        if (filter_var($mediaUrl, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Malformed media url');
        }
        $response = Request::get(rtrim($mediaUrl, '/') . '/?__a=1');
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Media with given code does not exist or account is private.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        $mediaArray = json_decode($response->raw_body, true);
        if (!isset($mediaArray['media'])) {
            throw new InstagramException('Media with this code does not exist');
        }
        return Media::fromMediaPage($mediaArray['media']);
    }

    public function getMediasByTag($tag, $count = 12, $maxId = '')
    {
        $index = 0;
        $medias = [];
        $hasNextPage = true;
        while ($index < $count && $hasNextPage) {
            $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId));
            if ($response->code !== 200) {
                throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
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

    public function searchAccountsByUsername($username)
    {
        $response = Request::get(Endpoints::getGeneralSearchJsonLink($username));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
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

    public function searchTagsByTagName($tag)
    {
        $response = Request::get(Endpoints::getGeneralSearchJsonLink($tag));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
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

    public function getTopMediasByTagName($tagName)
    {
        $response = Request::get(Endpoints::getMediasJsonByTagLink($tagName, ''));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        $jsonResponse = json_decode($response->raw_body, true);
        $medias = [];
        foreach ($jsonResponse['tag']['top_posts']['nodes'] as $mediaArray) {
            $medias[] = Media::fromTagPage($mediaArray);
        }
        return $medias;
    }

    public function getMediaById($mediaId)
    {
        $mediaLink = Media::getLinkFromId($mediaId);
        return self::getMediaByUrl($mediaLink);
    }

    public function getMediaCommentsByCode($code, $count = 10, $maxId = null)
    {
        $numberOfCommentsToRetreive = $count;
        $index = 0;
        $maxCommentsPerRequest = 9999;
        $remain = 0;
        if ($count > $maxCommentsPerRequest) {
            $remain = $count - $maxCommentsPerRequest;
            $count = $maxCommentsPerRequest;
        }

        if (!isset($maxId)) {
            $response = Request::get(Endpoints::getLastCommentsByCodeLink($code, $count));
        } else {
            $response = Request::get(Endpoints::getCommentsBeforeCommentIdByCode($code, $count, $maxId));
        }

        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        $jsonResponse = json_decode($response->raw_body, true);
        $commentsCount = $jsonResponse['comments']['count'];
        $nodes = $jsonResponse['comments']['nodes'];
        $comments = [];
        foreach ($nodes as $commentArray) {
            $comments[] = Comment::fromApi($commentArray);
        }

        if ($count <= $maxCommentsPerRequest) {
            return $comments;
        }
        $index = $count;
        $hasPrevious = $jsonResponse['comments']['page_info']['has_previous_page'];
        $maxId = $jsonResponse['comments']['nodes'][$index - 1]['id'];
        while ($hasPrevious && $index < $numberOfCommentsToRetreive) {
            if ($remain > $maxCommentsPerRequest) {
                $response = Request::get(Endpoints::getCommentsBeforeCommentIdByCode($code, $maxCommentsPerRequest, $maxId));
                $remain -= $maxCommentsPerRequest;
            } else {
                $response = Request::get(Endpoints::getCommentsBeforeCommentIdByCode($code, $remain, $maxId));
            }

        }
        return $comments;


    }
}