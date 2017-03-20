<?php

namespace InstagramScraper;

use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Model\Account;
use InstagramScraper\Model\Comment;
use InstagramScraper\Model\Location;
use InstagramScraper\Model\Media;
use InstagramScraper\Model\Tag;
use phpFastCache\CacheManager;
use Unirest\Request;

class Instagram
{
    const MAX_COMMENTS_PER_REQUEST = 300;
    private static $instanceCache;
    public $sessionUsername;
    public $sessionPassword;
    public $sessionId;
    public $mid;
    public $csrfToken;

    public function __construct()
    {
    }

    public static function withCredentials($username, $password, $sessionFolder = null)
    {
        if (is_null($sessionFolder)) {
            $sessionFolder = __DIR__ . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR;
        }
        // load cache
        CacheManager::setDefaultConfig([
            'path' => $sessionFolder
        ]);
        self::$instanceCache = CacheManager::getInstance('files');
        $instance = new self();
        $instance->sessionUsername = $username;
        $instance->sessionPassword = $password;
        return $instance;
    }

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
                $parameters = Endpoints::getLastCommentsByCodeLink($code, $numberOfCommentsToRetreive);

            } else {
                $parameters = Endpoints::getCommentsBeforeCommentIdByCode($code, $numberOfCommentsToRetreive, $maxId);
            }
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

    private function getContentsFromUrl($parameters)
    {
        if (!function_exists('curl_init')) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Endpoints::INSTAGRAM_QUERY_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'q=' . $parameters);
        $headers = array();
        $headers[] = "Cookie:  csrftoken=$this->csrfToken; sessionid=$this->sessionId";
        $headers[] = "X-Csrftoken: $this->csrfToken";
        $headers[] = "Referer: https://www.instagram.com/";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function getAccountById($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('User id must be integer or integer wrapped in string');
        }
        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();

        $parameters = Endpoints::getAccountJsonInfoLinkByAccountId($id);

        $response = Request::post(Endpoints::INSTAGRAM_QUERY_URL, $this->generateHeaders($session), ['q' => $parameters]);

        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        $cookies = self::parseCookies($response->headers['Set-Cookie']);
        $this->csrfToken = $cookies['csrftoken'];
        $session['csrftoken'] = $this->csrfToken;
        $cachedString->set($session);
        self::$instanceCache->save($cachedString);

        $userArray = json_decode($response->raw_body, true);
        if ($userArray['status'] === 'fail') {
            throw new InstagramException($userArray['message']);
        }
        if (!isset($userArray['username'])) {
            throw new InstagramNotFoundException('User with this id not found');
        }
        return Account::fromAccountPage($userArray);
    }

    private function generateHeaders($session)
    {
        $cookies = '';
        foreach ($session as $key => $value) {
            $cookies .= "$key=$value; ";
        }
        $headers = ['cookie' => $cookies, 'referer' => Endpoints::BASE_URL . '/', 'x-csrftoken' => $this->csrfToken];
        return $headers;
    }

    private static function parseCookies($rawCookies)
    {
        $cookies = [];
        foreach ($rawCookies as $c) {
            $c = explode(';', $c)[0];
            $parts = explode('=', $c);
            if (sizeof($parts) >= 2 && !is_null($parts[1])) {
                $cookies[$parts[0]] = $parts[1];
            }
        }
        return $cookies;
    }

    public function getMediasByTag($tag, $count = 12, $maxId = '')
    {
        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();
        $index = 0;
        $medias = [];
        $mediaIds = [];
        $hasNextPage = true;
        while ($index < $count && $hasNextPage) {
            $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId), $this->generateHeaders($session));
            if ($response->code !== 200) {
                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
            }
            $cookies = self::parseCookies($response->headers['Set-Cookie']);
            $this->csrfToken = $cookies['csrftoken'];
            $session['csrftoken'] = $this->csrfToken;
            $cachedString->set($session);
            self::$instanceCache->save($cachedString);
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
                $media = Media::fromTagPage($mediaArray);
                if (in_array($media->id, $mediaIds)) {
                    return $medias;
                }
                $mediaIds[] = $media->id;
                $medias[] = $media;
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

    public function getPaginateMediasByTag($tag, $maxId = '')
    {
        $hasNextPage = true;
        $medias = [];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage
        ];
        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();
        $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId), $this->generateHeaders($session));

        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }

        $cookies = self::parseCookies($response->headers['Set-Cookie']);
        $this->csrfToken = $cookies['csrftoken'];
        $session['csrftoken'] = $this->csrfToken;
        $cachedString->set($session);
        self::$instanceCache->save($cachedString);

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
        $count = $arr['tag']['media']['count'];

        $toReturn = [
            'medias' => $medias,
            'count' => $count,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        return $toReturn;
    }

    public function getTopMediasByTagName($tagName)
    {
        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();
        $response = Request::get(Endpoints::getMediasJsonByTagLink($tagName, ''), $this->generateHeaders($session));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $cookies = self::parseCookies($response->headers['Set-Cookie']);
        $this->csrfToken = $cookies['csrftoken'];
        $session['csrftoken'] = $this->csrfToken;
        $cachedString->set($session);
        self::$instanceCache->save($cachedString);
        $jsonResponse = json_decode($response->raw_body, true);
        $medias = [];
        foreach ($jsonResponse['tag']['top_posts']['nodes'] as $mediaArray) {
            $medias[] = Media::fromTagPage($mediaArray);
        }
        return $medias;
    }

    public function getLocationTopMediasById($facebookLocationId)
    {
        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();
        $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId), $this->generateHeaders($session));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Location with this id doesn\'t exist');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $cookies = self::parseCookies($response->headers['Set-Cookie']);
        $this->csrfToken = $cookies['csrftoken'];
        $session['csrftoken'] = $this->csrfToken;
        $cachedString->set($session);
        self::$instanceCache->save($cachedString);
        $jsonResponse = json_decode($response->raw_body, true);
        $nodes = $jsonResponse['location']['top_posts']['nodes'];
        $medias = [];
        foreach ($nodes as $mediaArray) {
            $medias[] = Media::fromTagPage($mediaArray);
        }
        return $medias;
    }

    public function getLocationMediasById($facebookLocationId, $quantity = 12, $offset = '')
    {
        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();

        $index = 0;
        $medias = [];
        $hasNext = true;
        while ($index < $quantity && $hasNext) {
            $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId, $offset), $this->generateHeaders($session));
            if ($response->code !== 200) {
                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
            }
            $cookies = self::parseCookies($response->headers['Set-Cookie']);
            $this->csrfToken = $cookies['csrftoken'];
            $session['csrftoken'] = $this->csrfToken;
            $cachedString->set($session);
            self::$instanceCache->save($cachedString);
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

    public function getLocationById($facebookLocationId)
    {
        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();

        $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId), $this->generateHeaders($session));
        if ($response->code === 404) {
            throw new InstagramNotFoundException('Location with this id doesn\'t exist');
        }
        if ($response->code !== 200) {
            throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
        }
        $cookies = self::parseCookies($response->headers['Set-Cookie']);
        $this->csrfToken = $cookies['csrftoken'];
        $session['csrftoken'] = $this->csrfToken;
        $cachedString->set($session);
        self::$instanceCache->save($cachedString);
        $jsonResponse = json_decode($response->raw_body, true);
        return Location::makeLocation($jsonResponse['location']);
    }

    public function login($force = false)
    {
        if ($this->sessionUsername == null || $this->sessionPassword == null) {
            throw new InstagramAuthException("User credentials not provided");
        }

        $cachedString = self::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();
        if ($force || !$this->isLoggedIn($session)) {
            $response = Request::get(Endpoints::BASE_URL);
            if ($response->code !== 200) {
                throw new InstagramException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
            }
            $cookies = self::parseCookies($response->headers['Set-Cookie']);
            $this->csrfToken = $cookies['csrftoken'];
            $this->mid = $cookies['mid'];
            $headers = ['cookie' => "csrftoken=$this->csrfToken; mid=$this->mid;", 'referer' => Endpoints::BASE_URL . '/', 'x-csrftoken' => $this->csrfToken];
            $response = Request::post(Endpoints::LOGIN_URL, $headers, ['username' => $this->sessionUsername, 'password' => $this->sessionPassword]);
            if ($response->code !== 200) {
                throw new InstagramAuthException('Response code is ' . $response->code . '. Body: ' . $response->body . ' Something went wrong. Please report issue.');
            }
            $cookies = self::parseCookies($response->headers['Set-Cookie']);
            $cookies['mid'] = $this->mid;
            $this->csrfToken = $cookies['csrftoken'];
            $this->sessionId = $cookies['sessionid'];
            $cachedString->set($cookies);
            self::$instanceCache->save($cachedString);
        }
    }

    public function isLoggedIn($session)
    {
        if (is_null($session) || !isset($session['sessionid'])) {
            return false;
        }
        $this->sessionId = $session['sessionid'];
        $this->csrfToken = $session['csrftoken'];
        $headers = ['cookie' => "csrftoken=$this->csrfToken; sessionid=$this->sessionId;", 'referer' => Endpoints::BASE_URL . '/', 'x-csrftoken' => $this->csrfToken];
        $response = Request::get(Endpoints::BASE_URL, $headers);
        if ($response->code !== 200) {
            return false;
        }
        $cookies = self::parseCookies($response->headers['Set-Cookie']);
        if (!isset($cookies['ds_user_id'])) {
            return false;
        }
        return true;
    }
}