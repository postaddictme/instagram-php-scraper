<?php

namespace InstagramScraper;

use Exception;
use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\Exception\InstagramChallengeRecaptchaException;
use InstagramScraper\Exception\InstagramChallengeSubmitPhoneNumberException;
use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Exception\InstagramAgeRestrictedException;
use InstagramScraper\Http\Response;
use InstagramScraper\Model\Account;
use InstagramScraper\Model\Activity;
use InstagramScraper\Model\Comment;
use InstagramScraper\Model\Like;
use InstagramScraper\Model\Location;
use InstagramScraper\Model\Media;
use InstagramScraper\Model\Story;
use InstagramScraper\Model\Tag;
use InstagramScraper\Model\Thread;
use InstagramScraper\Model\UserStories;
use InstagramScraper\Model\Highlight;
use InstagramScraper\TwoStepVerification\ConsoleVerification;
use InstagramScraper\TwoStepVerification\TwoStepVerificationInterface;
use InvalidArgumentException;
use InstagramScraper\Http\Request;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;
use stdClass;

class Instagram
{
    const HTTP_NOT_FOUND = 404;
    const HTTP_OK = 200;
    const HTTP_FOUND = 302;
    const HTTP_FORBIDDEN = 403;
    const HTTP_BAD_REQUEST = 400;

    const MAX_COMMENTS_PER_REQUEST = 300;
    const MAX_LIKES_PER_REQUEST = 300;
    const PAGING_TIME_LIMIT_SEC = 1800; // 30 mins time limit on operations that require multiple requests
    const PAGING_DELAY_MINIMUM_MICROSEC = 1000000; // 1 sec min delay to simulate browser
    const PAGING_DELAY_MAXIMUM_MICROSEC = 3000000; // 3 sec max delay to simulate browser

    const X_IG_APP_ID = '936619743392459';

    /** @var CacheInterface $instanceCache */
    protected static $instanceCache = null;

    public $pagingTimeLimitSec = self::PAGING_TIME_LIMIT_SEC;
    public $pagingDelayMinimumMicrosec = self::PAGING_DELAY_MINIMUM_MICROSEC;
    public $pagingDelayMaximumMicrosec = self::PAGING_DELAY_MAXIMUM_MICROSEC;
    protected $sessionUsername;
    protected $sessionPassword;
    protected $userSession;
    protected $rhxGis = null;
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36';
    protected $customCookies = null;

    /**
     * Instagram constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        Request::setHttpClient($client);
    }

    /**
     * @param ClientInterface $httpClient
     */
    public static function setHttpClient(ClientInterface $httpClient): void
    {
        Request::setHttpClient($httpClient);
    }

    /**
     * @param ClientInterface $client
     * @param string $username
     * @param string $password
     * @param CacheInterface $cache
     *
     * @return Instagram
     */
    public static function withCredentials(ClientInterface $client, $username, $password, $cache)
    {
        static::$instanceCache = $cache;
        $instance = new self($client);
        $instance->sessionUsername = $username;
        $instance->sessionPassword = $password;
        return $instance;
    }

    /**
     * @param ClientInterface $client
     * @param string $username
     * @param CacheInterface $cache
     *
     * @return Instagram
     */
    public static function withUsername(ClientInterface $client, $username, $cache)
    {
        static::$instanceCache = $cache;
        $instance = new self($client);
        $instance->sessionUsername = $username;
        return $instance;
    }

    public function loadSession()
    {
        $session = static::$instanceCache->get($this->getCacheKey());
        $this->userSession = $session;
        return $session;
    }
    /**
     * @param string $tag
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public  function searchTagsByTagName($tag)
    {
        // TODO: Add tests and auth
        $response = Request::get(Endpoints::getGeneralSearchJsonLink($tag), $this->generateHeaders($this->userSession));

        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }

        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = json_decode($response->raw_body, true, 512, JSON_BIGINT_AS_STRING);
        if (!isset($jsonResponse['status']) || $jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }

        if (!isset($jsonResponse['hashtags']) || empty($jsonResponse['hashtags'])) {
            return [];
        }
        $hashtags = [];
        foreach ($jsonResponse['hashtags'] as $jsonHashtag) {
            $hashtags[] = Tag::create($jsonHashtag['hashtag']);
        }
        return $hashtags;
    }

    /**
     * @param stdClass|string $rawError
     *
     * @return string
     */
    private static function getErrorBody($rawError)
    {
        if (is_string($rawError)) {
            return $rawError;
        }
        if (is_object($rawError)) {
            $str = '';
            foreach ($rawError as $key => $value) {
                //to avoid erros in an error function make sure all are string before concat operation
                if (is_array($value))  $value=json_encode((array)$value);
                if (is_object($value)) $value=json_encode((array)$value);
                if (is_array($key))    $key=json_encode((array)$key);
                if (is_object($key))   $key=json_encode((array)$key);
                $str .= ' ' . $key . ' => ' . $value . ';';
            }
            return $str;
        } else {
            return 'Unknown body format';
        }

    }

    public static function httpCodeToString($code)
    {
        switch($code) {
            case 100:
                return "Continue";
            case 101:
                return "Switching Protocols";
            case 102:
                return "Processing";
            case 200:
                return "OK";
            case 201:
                return "Created";
            case 202:
                return "Accepted";
            case 203:
                return "Non-Authoritative Information";
            case 204:
                return "No Content";
            case 205:
                return "Reset Content";
            case 206:
                return "Partial Content";
            case 207:
                return "Multi-Status";
            case 208:
                return "Already Reported";
            case 226:
                return "IM Used";
            case 300:
                return "Multiple Choices";
            case 301:
                return "Moved Permanently";
            case 302:
                return "Found";
            case 303:
                return "See Other";
            case 304:
                return "Not Modified";
            case 305:
                return "Use Proxy";
            case 306:
                return "Switch Proxy";
            case 307:
                return "Temporary Redirect";
            case 308:
                return "Permanent Redirect";
            case 400:
                return "Bad Request";
            case 401:
                return "Unauthorized";
            case 402:
                return "Payment Required";
            case 403:
                return "Forbidden";
            case 404:
                return "Not Found";
            case 405:
                return "Method Not Allowed";
            case 406:
                return "Not Acceptable";
            case 407:
                return "Proxy Authentication Required";
            case 408:
                return "Request Timeout";
            case 409:
                return "Conflict";
            case 410:
                return "Gone";
            case 411:
                return "Length Required";
            case 412:
                return "Precondition Failed";
            case 413:
                return "Payload Too Large";
            case 414:
                return "URI Too Long";
            case 415:
                return "Unsupported Media Type";
            case 416:
                return "Range Not Satisfiable";
            case 417:
                return "Expectation Failed";
            case 418:
                return "I'm a teapot";
            case 421:
                return "Misdirected Request";
            case 422:
                return "Unprocessable Entity";
            case 423:
                return "Locked";
            case 424:
                return "Failed Dependency";
            case 426:
                return "Upgrade Required";
            case 428:
                return "Precondition Required";
            case 429:
                return "Too Many Requests";
            case 431:
                return "Request Header Fields Too Large";
            case 450:
                return "Blocked by Windows Parental Controls";
            case 451:
                return "Unavailable For Legal Reasons";
            case 500:
                return "Internal Server Error";
            case 501:
                return "Not Implemented";
            case 502:
                return "Bad Gateway";
            case 503:
                return "Service Unavailable";
            case 504:
                return "Gateway Time-out";
            case 505:
                return "HTTP Version Not Supported";
            case 506:
                return "Variant Also Negotiaties";
            case 507:
                return "Insufficient Storage";
            case 508:
                return "Loop Detected";
            case 510:
                return "Not Extended";
            case 511:
                return "Network Authentication Required";
            default:
                return "Not implemented or invalid HTTP code";
        }
    }

    /**
     * Set how many media objects should be retrieved in a single request
     * @param int $count
     */
    public static function setAccountMediasRequestCount($count)
    {
        Endpoints::setAccountMediasRequestCount($count);
    }

    /**
     * @param string $username
     *
     * @return Account[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function searchAccountsByUsername($username, $count = 10)
    {
        $response = Request::get(Endpoints::getGeneralSearchJsonLink($username, $count), $this->generateHeaders($this->userSession));

        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if (!isset($jsonResponse['status']) || $jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        if (!isset($jsonResponse['users']) || empty($jsonResponse['users'])) {
            return [];
        }

        $accounts = [];
        foreach ($jsonResponse['users'] as $jsonAccount) {
            $accounts[] = Account::create($jsonAccount['user']);
        }
        return $accounts;
    }


/**
     * @param string $locationName
     *
     * @return Places[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function searchPlacesByLocationName($locationName, $count = 10)
    {
        $response = Request::get(Endpoints::getGeneralSearchJsonLink($locationName, $count), $this->generateHeaders($this->userSession));

        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Account with given Location does not exist.');
        }
        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);
        if (!isset($jsonResponse['status']) || $jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }
        if (!isset($jsonResponse['places']) || empty($jsonResponse['places'])) {
            return [];
        }

        $places = [];
        foreach ($jsonResponse['places'] as $jsonPlaces) {
            $places[] = Location::create($jsonPlaces['place']['location']);
        }
        return $places;
    }




    /**
     * @param $session
     * @param $gisToken
     *
     * @return array
     */
    private function generateHeaders($session, $gisToken = null)
    {
        $headers = [];
        if ($session) {
            $cookies = '';
            foreach ($session as $key => $value) {
                $cookies .= "$key=$value; ";
            }

            $csrf = empty($session['csrftoken']) ? $session['x-csrftoken'] : $session['csrftoken'];

            $headers = [
                'cookie' => $cookies,
                'referer' => Endpoints::BASE_URL . '/',
                'x-csrftoken' => $csrf,
            ];

        }

        if ($this->getUserAgent()) {
            $headers['user-agent'] = $this->getUserAgent();

            if (!is_null($gisToken)) {
                $headers['x-instagram-gis'] = $gisToken;
            }
        }

        if (empty($headers['x-csrftoken'])) {
            $headers['x-csrftoken'] = md5(uniqid()); // this can be whatever, insta doesn't like an empty value
        }

        return $headers;
    }

    /**
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param $userAgent
     *
     * @return string
     */
    public function setUserAgent($userAgent)
    {
        return $this->userAgent = $userAgent;
    }

    /**
     * @param $rawBody
     * @return mixed
     */
    private function decodeRawBodyToJson($rawBody)
    {
        return json_decode($rawBody, true, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * @return null
     */
    public function resetUserAgent()
    {
        return $this->userAgent = null;
    }

    /**
    * Gets logged user feed.
    *
    * @return     Media[]
    * @throws     InstagramNotFoundException
    *
    * @throws     InstagramException
    */
    public function getPaginateFeed($mediaCount=12, $maxId='', $commentCount=6, $likeCount=4) {
     return $this->getFeed($mediaCount, $maxId, $commentCount, $likeCount, true);
    }
    /**
    * Gets logged user feed.
    *
    * @return     Media[]
    * @throws     InstagramNotFoundException
    *
    * @throws     InstagramException
    */
    public function getFeed($mediaCount=12, $maxId='', $commentCount=6, $likeCount=4, $paginateInd=false)
    {
       $vars = [
         "fetch_media_item_count"=> $mediaCount,
         "fetch_comment_count"=> $commentCount,
         "fetch_media_item_cursor"=> $maxId,
         "fetch_like"=> $likeCount, //d
         "has_stories"=> true
       ];
       $endPoint = Endpoints::USER_FEED_hash . '&variables='.json_encode($vars);

       $response = Request::get($endPoint,
           $this->generateHeaders($this->userSession));

       if ($response->code === static::HTTP_NOT_FOUND) {
           throw new InstagramNotFoundException('Account with given username does not exist.');
       }
       if ($response->code !== static::HTTP_OK) {
           throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                        'Something went wrong. Please report issue.');
       }

       $this->parseCookies($response->headers);
       $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);
       $medias = [];
       $nodes = (array)@$jsonResponse['data']['user']["edge_web_feed_timeline"]['edges'];
       $count = 0;
       foreach ($nodes as $mediaArray) {
           $medias[] = Media::create($mediaArray['node']);
           $count++;
       }

       $count2 = 0;
       if (empty($jsonResponse['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'])) {
           $userStories = [];
       }
       else {
         $userStories=[];
         $nodes = (array)$jsonResponse['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'];
         $userStories = [];
         if (count($nodes)==0) {
           $userStories=[];
         }
         else {
           foreach ($nodes as $node) {
               $data = $node['node'];
               $UserStory = UserStories::create($data);
               $UserStory->setOwner(Account::create($data['user']));
               $userStories[] = $UserStory;
               $count2++;
           }
         }
       }

       if ($paginateInd) {
         $maxId       = $jsonResponse['data']['user']["edge_web_feed_timeline"]['page_info']['end_cursor'];
         $hasNextPage = $jsonResponse['data']['user']["edge_web_feed_timeline"]['page_info']['has_next_page'];
         $toReturn = [
             'medias' => $medias,
             'maxId' => $maxId,
             'media_count' => $count,
             'storie_count' => $count2,
             'userStorie_count' => $count2,
             'hasNextPage' => $hasNextPage,
             'userStories' => $userStories
           ];
           return $toReturn;
       }
       else return $medias;
    }
    /**
    *
    * @return array
    */
    public function getUserStories()
    {
     $variables = ['precomposed_overlay' => false, 'reel_ids' => []];
     $response = Request::get(Endpoints::getUserStoriesLink($variables), $this->generateHeaders($this->userSession));
     if ($response->code !== static::HTTP_OK) {
         throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                      'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
       }

     $arr = $this->decodeRawBodyToJson($response->raw_body);

     $userStories = [];
     if (!empty($arr['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges']))
     {
       $nodes = (array)$arr['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'];
       foreach ($nodes as $node) {
             $data = $node['node'];
             $UserStory = UserStories::create($data);
             $UserStory->setOwner(Account::create($data['user']));
             $userStories[] = $UserStory;
       }
     }

     return $userStories;
    }


    /**
     *
     * @return array
     */
    public function getCustomCookies()
    {
        return $this->customCookies;
    }

    /**
     * @param $cookies
     *
     * @return array
     */
    public function setCustomCookies($cookies)
    {
        return $this->customCookies = $cookies;
    }



    /**
     * We work only on https in this case if we have same cookies on Secure and not - we will choice Secure cookie
     *
     * @param array $headers
     *
     * @return array
     */
    private function parseCookies($headers)
    {
        if($this->customCookies){
            return $this->getCustomCookies();
        }

        $rawCookies = isset($headers['Set-Cookie']) ? $headers['Set-Cookie'] : (isset($headers['set-cookie']) ? $headers['set-cookie'] : []);

        if (!is_array($rawCookies)) {
            $rawCookies = [$rawCookies];
        }

        $not_secure_cookies = [];
        $secure_cookies = [];

        foreach ($rawCookies as $cookie) {
            $cookie_array = 'not_secure_cookies';
            $cookie_parts = explode(';', $cookie);
            foreach ($cookie_parts as $cookie_part) {
                if (trim($cookie_part) == 'Secure') {
                    $cookie_array = 'secure_cookies';
                    break;
                }
            }
            $value = array_shift($cookie_parts);
            $parts = explode('=', $value);
            if (sizeof($parts) >= 2 && !is_null($parts[1])) {
                ${$cookie_array}[$parts[0]] = $parts[1];
            }
        }

        $cookies = $secure_cookies + $not_secure_cookies;

        if (isset($cookies['csrftoken'])) {
            $this->userSession['csrftoken'] = $cookies['csrftoken'];
        }

        return $cookies;
    }

    /**
     * Gets logged user activity.
     *
     * @return     Activity
     * @throws     InstagramNotFoundException
     *
     * @throws     InstagramException
     */
    public function getActivity()
    {
        $response = Request::get(Endpoints::getActivityUrl(),
            $this->generateHeaders($this->userSession));

        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.');
        }

        $this->parseCookies($response->headers);
        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        return Activity::create((array)@$jsonResponse['graphql']['user']['activity_feed']);
    }

    /**
     * @param string $username
     * @param int $count
     * @param string $maxId
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getMedias($username, $count = 20, $maxId = '')
    {

        $account = $this->getAccountInfo($username);
        return $this->getMediasByUserId($account->getId(), $count, $maxId);
    }

    /**
     * @param string $username
     * @param int $count
     * @param string $maxId
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getTaggedMedias($username, $count = 20, $maxId = '')
    {

        $account = $this->getAccountInfo($username);
        return $this->getTaggedMediasByUserId($account->getId(), $count, $maxId);
    }

    /**
     * @param string $username
     *
     * @return Account
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getAccount($username)
    {
        $response = Request::get(Endpoints::getAccountPageLink($username), $this->generateHeaders($this->userSession));

        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $userArray = self::extractSharedDataFromBody($response->raw_body);

        if ($this->isAccountAgeRestricted($userArray, $response->raw_body)) {
            throw new InstagramAgeRestrictedException('Account with given username is age-restricted.');
        }

        if (!isset($userArray['entry_data']['ProfilePage'][0]['graphql']['user'])) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }
        return Account::create($userArray['entry_data']['ProfilePage'][0]['graphql']['user']);
    }

    public function getAccountInfo($username)
    {
        $response = Request::get(Endpoints::getAccountJsonLink($username), $this->generateHeaders($this->userSession));

        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $userArray = $this->decodeRawBodyToJson($response->raw_body);

        if (!isset($userArray['graphql']['user'])) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return Account::create($userArray['graphql']['user']);
    }

    private static function extractSharedDataFromBody($body)
    {
        if (preg_match_all('#\_sharedData \= (.*?)\;\<\/script\>#', $body, $out)) {
            return json_decode($out[1][0], true, 512, JSON_BIGINT_AS_STRING);
        }
        return null;
    }

    private function isAccountAgeRestricted($userArray, $body)
    {
        if ($userArray === null && strpos($body, '<h2>Restricted profile</h2>') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param int $id
     * @param int $count
     * @param string $maxId
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getMediasByUserId($id, $count = 12, $maxId = '')
    {
        $index = 0;
        $medias = [];
        $isMoreAvailable = true;
        while ($index < $count && $isMoreAvailable) {
            $variables = json_encode([
                'id' => (string)$id,
                'first' => (string)$count,
                'after' => (string)$maxId
            ]);

            $response = Request::get(Endpoints::getAccountMediasJsonLink($variables), $this->generateHeaders($this->userSession, $this->generateGisToken($variables)));

            if (static::HTTP_NOT_FOUND === $response->code) {
                throw new InstagramNotFoundException('Account with given id does not exist.');
            }
            if (static::HTTP_OK !== $response->code) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $arr = $this->decodeRawBodyToJson($response->raw_body);

            if (!is_array($arr)) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $nodes = $arr['data']['user']['edge_owner_to_timeline_media']['edges'];
            // fix - count takes longer/has more overhead
            if (!isset($nodes) || empty($nodes)) {
                return [];
            }
            foreach ($nodes as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                $medias[] = Media::create($mediaArray['node']);
                $index++;
            }
            $maxId = $arr['data']['user']['edge_owner_to_timeline_media']['page_info']['end_cursor'];
            $isMoreAvailable = $arr['data']['user']['edge_owner_to_timeline_media']['page_info']['has_next_page'];
        }
        return $medias;
    }

    /**
     * @param int $id
     * @param int $count
     * @param string $maxId
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getTaggedMediasByUserId($id, $count = 12, $maxId = '')
    {
        $index = 0;
        $medias = [];
        $isMoreAvailable = true;
        while ($index < $count && $isMoreAvailable) {
            $variables = json_encode([
                'id' => (string)$id,
                'first' => (string)$count,
                'after' => (string)$maxId
            ]);

            $response = Request::get(Endpoints::getAccountTaggedMediasJsonLink($variables), $this->generateHeaders($this->userSession, $this->generateGisToken($variables)));

            if (static::HTTP_NOT_FOUND === $response->code) {
                throw new InstagramNotFoundException('Account with given id does not exist.');
            }
            if (static::HTTP_OK !== $response->code) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $arr = $this->decodeRawBodyToJson($response->raw_body);

            if (!is_array($arr)) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $nodes = $arr['data']['user']['edge_user_to_photos_of_you']['edges'];
            // fix - count takes longer/has more overhead
            if (!isset($nodes) || empty($nodes)) {
                return [];
            }
            foreach ($nodes as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                $medias[] = Media::create($mediaArray['node']);
                $index++;
            }
            $maxId = $arr['data']['user']['edge_user_to_photos_of_you']['page_info']['end_cursor'];
            $isMoreAvailable = $arr['data']['user']['edge_user_to_photos_of_you']['page_info']['has_next_page'];
        }
        return $medias;
    }

    /**
     * @param $variables
     * @return string
     * @throws InstagramException
     */
    private function generateGisToken($variables)
    {
        return null;
//        return md5(implode(':', [$this->getRhxGis(), $variables]));
    }

    /**
     * @param string $username
     * @param int $count
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getMediasFromFeed($username, $count = 20)
    {
        $medias = [];
        $index = 0;
        $response = Request::get(Endpoints::getAccountJsonLink($username), $this->generateHeaders($this->userSession));

        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $userArray = $this->decodeRawBodyToJson($response->raw_body);

        if (!isset($userArray['graphql']['user'])) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $nodes = $userArray['graphql']['user']['edge_owner_to_timeline_media']['edges'];

        if (!isset($nodes) || empty($nodes)) {
            return [];
        }

        foreach ($nodes as $mediaArray) {
            if ($index === $count) {
                return $medias;
            }
            $medias[] = Media::create($mediaArray['node']);
            $index++;
        }

        return $medias;
    }

    /**
     * @param $mediaId
     *
     * @return Media
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getMediaById($mediaId)
    {
        $mediaLink = Media::getLinkFromId($mediaId);
        return $this->getMediaByUrl($mediaLink);
    }

    /**
     * @param string $mediaUrl
     *
     * @return Media
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getMediaByUrl($mediaUrl)
    {
        if (filter_var($mediaUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Malformed media url');
        }
        $response = Request::get(rtrim($mediaUrl, '/') . '/?__a=1&__d=dis', $this->generateHeaders($this->userSession));

        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Media with given code does not exist or account is private.');
        }

        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $mediaArray = $this->decodeRawBodyToJson($response->raw_body);
        if (!isset($mediaArray['graphql']['shortcode_media']) && !isset($mediaArray['items'])) {
            throw new InstagramException('Media with this code does not exist');
        }
        if (isset($mediaArray['graphql']['shortcode_media'])) {
            return Media::create($mediaArray['graphql']['shortcode_media']);
        } else {
            return Media::create(current($mediaArray['items']));
        }
    }

    /**
     * @param string $mediaCode (for example BHaRdodBouH)
     *
     * @return Media
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */

    public function getMediaByCode($mediaCode)
    {
        $url = Endpoints::getMediaPageLink($mediaCode);
        return $this->getMediaByUrl($url);

    }

    /**
     * @param string $username
     * @param string $maxId
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateMedias($username, $maxId = '')
    {
        $account = $this->getAccountInfo($username);

        return $this->getPaginateMediasByUserId(
            $account->getId(),
            Endpoints::getAccountMediasRequestCount(),
            $maxId
        );
    }

    /**
     * @param int $id
     * @param int $count
     * @param string $maxId
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateMediasByUserId($id, $count = 12, $maxId = '')
    {
        $index = 0;
        $hasNextPage = true;
        $medias = [];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        while ($index < $count && $hasNextPage) {
            $variables = json_encode([
                'id' => (string)$id,
                'first' => (string)$count,
                'after' => (string)$maxId
            ]);

            $response = Request::get(
                Endpoints::getAccountMediasJsonLink($variables),
                $this->generateHeaders($this->userSession, $this->generateGisToken($variables))
            );

            if (static::HTTP_NOT_FOUND === $response->code) {
                throw new InstagramNotFoundException('Account with given id does not exist.');
            }

            if (static::HTTP_OK !== $response->code) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $arr = $this->decodeRawBodyToJson($response->raw_body);

            if (!is_array($arr)) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $nodes = $arr['data']['user']['edge_owner_to_timeline_media']['edges'];

            //if (count($arr['items']) === 0) {
            // I generally use empty. Im not sure why people would use count really - If the array is large then count takes longer/has more overhead.
            // If you simply need to know whether or not the array is empty then use empty.
            if (empty($nodes)) {
                return $toReturn;
            }

            foreach ($nodes as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                $medias[] = Media::create($mediaArray['node']);
                $index++;
            }

            $maxId = $arr['data']['user']['edge_owner_to_timeline_media']['page_info']['end_cursor'];
            $hasNextPage = $arr['data']['user']['edge_owner_to_timeline_media']['page_info']['has_next_page'];
        }

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        return $toReturn;
    }

    /**
     * @param $mediaId
     * @param int $count
     * @param null $maxId
     *
     * @return Comment[]
     * @throws InstagramException
     */
    public function getMediaCommentsById($mediaId, $count = 10, $maxId = null)
    {
        $code = Media::getCodeFromId($mediaId);
        return static::getMediaCommentsByCode($code, $count, $maxId);
    }

    /**
     * @param $mediaId
     * @param int $count
     * @param null $maxId
     *
     * @return Comment[]
     * @throws InstagramException
     */
    public function getPaginateMediaCommentsById($mediaId, $count = 10, $maxId = null)
    {
        $code = Media::getCodeFromId($mediaId);
        return static::getMediaCommentsByCode($code, $count, $maxId, true);
    }

    /**
      * @param $mediaId
      * @param int $count
      * @param null $maxId
      *
      * @return Comment[]
      * @throws InstagramException
      */
     public function getPaginateMediaCommentsByCode($code, $count = 10, $maxId = null)
     {
         return static::getMediaCommentsByCode($code, $count, $maxId, true);
     }

     /**
      * @param      $code
      * @param int  $count
      * @param null $maxId
      * @param bool $paginateInd (=false)
      *
      * @return Comment[]
      * @throws InstagramException
      */
    public function getMediaCommentsByCode($code, $count = 10, $maxId = null, $paginateInd = false)
    {
        $comments = [];
        $index = 0;
        $hasPrevious = true;
        while ($hasPrevious && $index < $count) {
            if ($count - $index > static::MAX_COMMENTS_PER_REQUEST) {
                $numberOfCommentsToRetrieve = static::MAX_COMMENTS_PER_REQUEST;
            } else {
                $numberOfCommentsToRetrieve = $count - $index;
            }

            $variables = json_encode([
                'shortcode' => (string)$code,
                'first' => (string)$numberOfCommentsToRetrieve,
                'after' => (string)$maxId
            ]);

            $commentsUrl = Endpoints::getCommentsBeforeCommentIdByCode($variables);
            $response = Request::get($commentsUrl, $this->generateHeaders($this->userSession, $this->generateGisToken($variables)));

            if (static::HTTP_OK !== $response->code) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }
            $this->parseCookies($response->headers);
            $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

            if (
                !isset($jsonResponse['data']['shortcode_media']['edge_media_to_comment']['edges'])
                || !isset($jsonResponse['data']['shortcode_media']['edge_media_to_comment']['count'])
                || !isset($jsonResponse['data']['shortcode_media']['edge_media_to_comment']['page_info']['has_next_page'])
                || !array_key_exists('end_cursor', $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['page_info'])
            ) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $nodes = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['edges'];
            $hasPrevious = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['page_info']['has_next_page'];
            $numberOfComments = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['count'];
            $maxId = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['page_info']['end_cursor'];

            if (empty($nodes) && $numberOfComments === 0) {
                break;
            }

            foreach ($nodes as $commentArray) {
                $comments[] = Comment::create($commentArray['node']);
                $index++;
            }

            if ($count > $numberOfComments) {
                $count = $numberOfComments;
            }
        }
        if ($paginateInd)
        {
          $ret = new \stdClass();
          $ret->hasPrevious      = $hasPrevious;
          $ret->commentsCount    = $numberOfComments;
          $ret->maxId            = $maxId;
          $ret->comments         = $comments;

          return $ret;
        }
        else return $comments;
    }

    /**
     * @param      $code
     * @param int $count
     * @param null $maxId
     *
     * @return array
     * @throws InstagramException
     */
    public function getMediaLikesByCode($code, $count = 10, $maxId = null)
    {
        $remain = $count;
        $likes = [];
        $index = 0;
        $hasPrevious = true;
        while ($hasPrevious && $index < $count) {
            if ($remain > self::MAX_LIKES_PER_REQUEST) {
                $numberOfLikesToRetreive = self::MAX_LIKES_PER_REQUEST;
                $remain -= self::MAX_LIKES_PER_REQUEST;
                $index += self::MAX_LIKES_PER_REQUEST;
            } else {
                $numberOfLikesToRetreive = $remain;
                $index += $remain;
                $remain = 0;
            }
            if (!isset($maxId)) {
                $maxId = '';

            }
            $commentsUrl = Endpoints::getLastLikesByCode($code, $numberOfLikesToRetreive, $maxId);
            $response = Request::get($commentsUrl, $this->generateHeaders($this->userSession));
            if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }
            $this->parseCookies($response->headers);

            $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

            $nodes = $jsonResponse['data']['shortcode_media']['edge_liked_by']['edges'];
            if (empty($nodes)) {
                return [];
            }

            foreach ($nodes as $likesArray) {
                $likes[] = Like::create($likesArray['node']);
            }

            $hasPrevious = $jsonResponse['data']['shortcode_media']['edge_liked_by']['page_info']['has_next_page'];
            $numberOfLikes = $jsonResponse['data']['shortcode_media']['edge_liked_by']['count'];
            if ($count > $numberOfLikes) {
                $count = $numberOfLikes;
            }
            if (sizeof($nodes) == 0) {
                return $likes;
            }
            $maxId = $jsonResponse['data']['shortcode_media']['edge_liked_by']['page_info']['end_cursor'];
        }

        return $likes;
    }

    /**
     * @param string $id
     *
     * @return Account
     * @throws InstagramException
     * @throws InvalidArgumentException
     * @throws InstagramNotFoundException
     */
    public function getAccountById($id)
    {
        $username = $this->getUsernameById($id);
        return $this->getAccount($username);
    }

    /**
     * @param string $id
     *
     * @return Account
     * @throws InstagramException
     * @throws InvalidArgumentException
     * @throws InstagramNotFoundException
     */
    public function getAccountInfoById($id)
    {
        $username = $this->getUsernameById($id);
        return $this->getAccountInfo($username);
    }

    /**
     * @param string $id
     * @return string
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getUsernameById($id)
    {
        $privateInfo = $this->getAccountPrivateInfo($id);
        return $privateInfo['username'];
    }

    /**
     * @param string $id
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getAccountPrivateInfo($id)
    {
        $response = Request::get(Endpoints::getAccountJsonPrivateInfoLinkByAccountId($id), $this->generateHeaders($this->userSession));
        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException('Failed to fetch account with given id');
        }

        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        if (!($responseArray = json_decode($response->raw_body, true))) {
            throw new InstagramException('Response does not JSON');
        }

        if ($responseArray['data']['user'] === null){
            throw new InstagramNotFoundException('Failed to fetch account with given id');
        }

        if ($responseArray['status'] !== 'ok') {
            throw new InstagramException((isset($responseArray['message']) ? $responseArray['message'] : 'Unknown Error'));
        }

        return $responseArray['data']['user']['reel']['user'];
    }

    /**
     * @param string $tag
     * @param int $count
     * @param string $maxId
     * @param string $minTimestamp
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getMediasByTag($tag, $count = 12, $maxId = '', $minTimestamp = null)
    {
        $index = 0;
        $medias = [];
        $mediaIds = [];
        $hasNextPage = true;
        while ($index < $count && $hasNextPage) {
            $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId),
                $this->generateHeaders($this->userSession));
            if ($response->code === static::HTTP_NOT_FOUND) {
                throw new InstagramNotFoundException('This tag does not exists or it has been hidden by Instagram');
            }
            if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue. Body: ' . static::getErrorBody($response->body)
                                            , $response->code, static::getErrorBody($response->body));
            }

            $this->parseCookies($response->headers);

            $arr = $this->decodeRawBodyToJson($response->raw_body);

            if (!is_array($arr)) {
                throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
            }
            $rootKey = array_key_exists('graphql', $arr) ? 'graphql' : 'data';

            if (empty($arr[$rootKey]['media_count'])) {
                return [];
            }

            $payload = $arr[$rootKey]['recent']['sections'];
            $nodes = array();
            foreach($payload as $p){
                $nodes = array_merge($nodes,$p['layout_content']['medias']);
            }

            foreach ($nodes as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                $media = Media::create($mediaArray['media']);
                if (in_array($media->getId(), $mediaIds)) {
                    return $medias;
                }
                if (isset($minTimestamp) && $media->getCreatedTime() < $minTimestamp) {
                    return $medias;
                }
                $mediaIds[] = $media->getId();
                $medias[] = $media;
                $index++;
            }
            if (empty($nodes)) {
                return $medias;
            }
            $maxId = $arr[$rootKey]['recent']['next_max_id']; // $arr[$rootKey]['hashtag']['edge_hashtag_to_media']['page_info']['end_cursor'];
            $hasNextPage = $arr[$rootKey]['recent']['more_available']; // $arr[$rootKey]['hashtag']['edge_hashtag_to_media']['page_info']['has_next_page'];
        }
        return $medias;
    }

    /**
     * @param string $tag
     * @param string $maxId
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateMediasByTag($tag, $maxId = '')
    {
        $hasNextPage = false;
        $medias = [];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId),
            $this->generateHeaders($this->userSession));

        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('This tag does not exists or it has been hidden by Instagram');
        }

        if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue. Body: ' . static::getErrorBody($response->body)
                                            , $response->code, static::getErrorBody($response->body));
        }

        $this->parseCookies($response->headers);

        $arr = $this->decodeRawBodyToJson($response->raw_body);

        if (!is_array($arr)) {
            throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
        }
        $rootKey = array_key_exists('graphql', $arr) ? 'graphql' : 'data';

        if (empty($arr[$rootKey]['media_count'])) {
            return $toReturn;
        }

        $payload = $arr[$rootKey]['recent']['sections'];
        $nodes = array();
        foreach($payload as $p){
            $nodes = array_merge($nodes,$p['layout_content']['medias']);
        }

        if (empty($nodes)) {
            return $toReturn;
        }

        foreach ($nodes as $mediaArray) {
            $medias[] = Media::create($mediaArray['media']);
        }

        $maxId = $arr[$rootKey]['recent']['next_max_id']; // $arr['data']['hashtag']['edge_hashtag_to_media']['page_info']['end_cursor'];
        $hasNextPage = $arr[$rootKey]['recent']['more_available']; // $arr['graphql']['hashtag']['edge_hashtag_to_media']['page_info']['has_next_page'];
        $count = $arr[$rootKey]['media_count']; // $arr['data']['hashtag']['edge_hashtag_to_media']['count'];

        $toReturn = [
            'medias' => $medias,
            'count' => $count,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        return $toReturn;
    }

    /**
     * @param string $facebookLocationId
     * @param string $maxId
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateMediasByLocationId($facebookLocationId, $maxId = '')
    {
        $hasNextPage = true;
        $medias = [];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId, $maxId),
            $this->generateHeaders($this->userSession));

        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Location with this id doesn\'t exist');
        }

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $this->parseCookies($response->headers);

        $arr = $this->decodeRawBodyToJson($response->raw_body);

        if (!is_array($arr)) {
            throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
        }

        if (empty($arr['graphql']['location']['edge_location_to_media']['count'])) {
            return $toReturn;
        }

        $nodes = $arr['graphql']['location']['edge_location_to_media']['edges'];

        if (empty($nodes)) {
            return $toReturn;
        }

        $slug = $arr['graphql']['location']['slug'];
        $name = $arr['graphql']['location']['name'];
        $rvd_i = 0;
        foreach ($nodes as $mediaArray) {
            $medias[$rvd_i] = Media::create($mediaArray['node']);
            $medias[$rvd_i] -> setLocationName($name);
            $medias[$rvd_i] -> setLocationSlug($slug);
            $rvd_i++;
        }

        $maxId = $arr['graphql']['location']['edge_location_to_media']['page_info']['end_cursor'];
        $hasNextPage = $arr['graphql']['location']['edge_location_to_media']['page_info']['has_next_page'];
        $count = $arr['graphql']['location']['edge_location_to_media']['count'];

        $toReturn = [
            'medias' => $medias,
            'count' => $count,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        return $toReturn;
    }

    /**
     * @param $tagName
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getCurrentTopMediasByTagName($tagName)
    {
        $response = Request::get(Endpoints::getMediasJsonByTagLink($tagName, ''),
            $this->generateHeaders($this->userSession));

        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue. Body: ' . static::getErrorBody($response->body)
                                            , $response->code, static::getErrorBody($response->body));
        }

        $this->parseCookies($response->headers);
        
        $arr = $this->decodeRawBodyToJson($response->raw_body);

        if (!is_array($arr)) {
            throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
        }
        $rootKey = array_key_exists('graphql', $arr) ? 'graphql' : 'data';
        
        $medias = [];
        
        $payload = $arr[$rootKey]['top']['sections'];
        $nodes = array();
        foreach($payload as $p){
            $nodes = array_merge($nodes,$p['layout_content']['medias']);
        }
            
        foreach ($nodes as $mediaArray) {
            $medias[] = Media::create($mediaArray['media']);
        }
        return $medias;
    }

    /**
     * @param $facebookLocationId
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getCurrentTopMediasByLocationId($facebookLocationId)
    {
        $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId),
            $this->generateHeaders($this->userSession));
        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Location with this id doesn\'t exist');
        }
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }
        $this->parseCookies($response->headers);
        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);
        $nodes = $jsonResponse['graphql']['location']['edge_location_to_top_posts']['edges'];
        $medias = [];

        $slug = $jsonResponse['graphql']['location']['slug'];
        $name = $jsonResponse['graphql']['location']['name'];
        $rvd_i=0;
        foreach ($nodes as $mediaArray) {
            $medias[$rvd_i] = Media::create($mediaArray['node']);
            $medias[$rvd_i] -> setLocationName($name);
            $medias[$rvd_i] -> setLocationSlug($slug);
            $rvd_i++;
          }

      return $medias;
    }

    /**
     * @param string $facebookLocationId
     * @param int $quantity
     * @param string $offset
     *
     * @return Media[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getMediasByLocationId($facebookLocationId, $quantity = 24, $offset = '')
    {
        $index = 0;
        $medias = [];
        $hasNext = true;
        while ($index < $quantity && $hasNext) {
            $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId, $offset),
                $this->generateHeaders($this->userSession));
            if ($response->code === static::HTTP_NOT_FOUND) {
                throw new InstagramNotFoundException('Location with this id doesn\'t exist');
            }
            if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }
            $this->parseCookies($response->headers);
            $arr = $this->decodeRawBodyToJson($response->raw_body);
            $nodes = $arr['graphql']['location']['edge_location_to_media']['edges'];

            $slug = $arr['graphql']['location']['slug'];
            $name = $arr['graphql']['location']['name'];

            foreach ($nodes as $mediaArray) {
                if ($index === $quantity) {
                    return $medias;
                }
                $medias[] = Media::create($mediaArray['node']);
                $medias[$index] -> setLocationName($name);
                $medias[$index] -> setLocationSlug($slug);
                $index++;
            }
            if (empty($nodes)) {
                return $medias;
            }
            $hasNext = $arr['graphql']['location']['edge_location_to_media']['page_info']['has_next_page'];
            $offset = $arr['graphql']['location']['edge_location_to_media']['page_info']['end_cursor'];
        }
        return $medias;
    }

    /**
     * @param string $facebookLocationId
     *
     * @return Location
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getLocationById($facebookLocationId)
    {
        $response = Request::get(Endpoints::getMediasJsonByLocationIdLink($facebookLocationId),
            $this->generateHeaders($this->userSession));

        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Location with this id doesn\'t exist');
        }
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $this->parseCookies($response->headers);
        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);
        return Location::create($jsonResponse['native_location_data']['location_info']);
    }

    /**
     * @param string $accountId Account id of the profile to query
     * @param int $count Total followers to retrieve
     * @param int $pageSize Internal page size for pagination
     * @param bool $delayed Use random delay between requests to mimic browser behaviour
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getFollowers($accountId, $count = 20, $pageSize = 20, $delayed = true)
    {
        $result = $this->getPaginateFollowers($accountId, $count, $pageSize, $delayed, '');
        return $result['accounts'] ?? [];
    }

    /**
     * @param string $accountId Account id of the profile to query
     * @param int $count Total followers to retrieve
     * @param int $pageSize Internal page size for pagination
     * @param bool $delayed Use random delay between requests to mimic browser behaviour
     * @param bool $nextPage Use to paginate results (ontop of internal pagination)
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateFollowers($accountId, $count = 20, $pageSize = 20, $delayed = true, $nextPage = '')
    {
        if ($delayed) {
            set_time_limit($this->pagingTimeLimitSec);
        }

        $index = 0;
        $accounts = [];
        $endCursor = $nextPage;
        $lastPagingInfo = [];

        if ($count < $pageSize) {
            throw new InstagramException('Count must be greater than or equal to page size.');
        }

        while (true) {
            $response = Request::get(Endpoints::getFollowersJsonLink($accountId, $pageSize, $endCursor),
                $this->generateHeaders($this->userSession));
            if ($response->code === static::HTTP_NOT_FOUND) {
                throw new InstagramNotFoundException('Account with this id doesn\'t exist');
            }
            if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

            if ($jsonResponse['data']['user']['edge_followed_by']['count'] === 0) {
                return $accounts;
            }

            $edgesArray = $jsonResponse['data']['user']['edge_followed_by']['edges'];
            if (count($edgesArray) === 0) {
                throw new InstagramException('Failed to get followers of account id ' . $accountId . '. The account is private.', static::HTTP_FORBIDDEN);
            }

            $pageInfo = $jsonResponse['data']['user']['edge_followed_by']['page_info'];
            $lastPagingInfo = $pageInfo;
            if ($pageInfo['has_next_page']) {
                $endCursor = $pageInfo['end_cursor'];
                $hasNextPage = true;
            } else {
                $hasNextPage = false;
            }

            foreach ($edgesArray as $edge) {
                $accounts[] = $edge['node'];
                $index++;
                if ($index >= $count) {
                    break 2;
                }
            }

            if (!$hasNextPage) {
                break;
            }

            if ($delayed) {
                // Random wait between 1 and 3 sec to mimic browser
                $microsec = rand($this->pagingDelayMinimumMicrosec, $this->pagingDelayMaximumMicrosec);
                usleep($microsec);
            }
        }
        $toReturn = [
            'hasNextPage' => $lastPagingInfo['has_next_page'],
            'nextPage' => $lastPagingInfo['end_cursor'],
            'accounts' => $accounts
        ];
        return $toReturn;
    }

    /**
     * @param $accountId
     * @param int $pageSize
     * @param string $nextPage
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateAllFollowers($accountId, $pageSize = 20, $nextPage = '')
    {
        $response = Request::get(Endpoints::getFollowersJsonLink($accountId, $pageSize, $nextPage),
            $this->generateHeaders($this->userSession));
        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Account with this id doesn\'t exist');
        }
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        $count = $jsonResponse['data']['user']['edge_followed_by']['count'];
        if ($count === 0) {
            return [];
        }

        $edgesArray = $jsonResponse['data']['user']['edge_followed_by']['edges'];
        if (count($edgesArray) === 0) {
            throw new InstagramException('Failed to get followers of account id ' . $accountId . '. The account is private.', static::HTTP_FORBIDDEN);
        }

        $accounts = [];
        foreach ($edgesArray as $edge) {
            $accounts[] = $edge['node'];
        }

        $pageInfo = $jsonResponse['data']['user']['edge_followed_by']['page_info'];

        return [
            'count' => $count,
            'hasNextPage' => $pageInfo['has_next_page'],
            'nextPage' => $pageInfo['end_cursor'],
            'accounts' => $accounts
        ];
    }

    /**
     * @param string $accountId Account id of the profile to query
     * @param int $count Total followed accounts to retrieve
     * @param int $pageSize Internal page size for pagination
     * @param bool $delayed Use random delay between requests to mimic browser behaviour
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getFollowing($accountId, $count = 20, $pageSize = 20, $delayed = true)
    {
        $result = $this->getPaginateFollowing($accountId, $count, $pageSize, $delayed, '');
        return $result['accounts'] ?? [];
    }

    /**
     * @param string $accountId Account id of the profile to query
     * @param int $count Total followed accounts to retrieve
     * @param int $pageSize Internal page size for pagination
     * @param bool $delayed Use random delay between requests to mimic browser behaviour
     * @param bool $nextPage Use to paginate results (ontop of internal pagination)
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateFollowing($accountId, $count = 20, $pageSize = 20, $delayed = true, $nextPage = '')
    {
        if ($delayed) {
            set_time_limit($this->pagingTimeLimitSec);
        }

        $index = 0;
        $accounts = [];
        $endCursor = $nextPage;
        $lastPagingInfo = [];

        if ($count < $pageSize) {
            throw new InstagramException('Count must be greater than or equal to page size.');
        }

        while (true) {
            $response = Request::get(Endpoints::getFollowingJsonLink($accountId, $pageSize, $endCursor),
                $this->generateHeaders($this->userSession));
            if ($response->code === static::HTTP_NOT_FOUND) {
                throw new InstagramNotFoundException('Account with this id doesn\'t exist');
            }
            if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

            if ($jsonResponse['data']['user']['edge_follow']['count'] === 0) {
                return $accounts;
            }

            $edgesArray = $jsonResponse['data']['user']['edge_follow']['edges'];
            if ((count($edgesArray) === 0) && ($index === 0)) {
                throw new InstagramException('Failed to get followers of account id ' . $accountId . '. The account is private.', static::HTTP_FORBIDDEN);
            }

            $pageInfo = $jsonResponse['data']['user']['edge_follow']['page_info'];
            $lastPagingInfo = $pageInfo;
            if ($pageInfo['has_next_page']) {
                $endCursor = $pageInfo['end_cursor'];
                $hasNextPage = true;
            } else {
                $hasNextPage = false;
            }

            foreach ($edgesArray as $edge) {
                $accounts[] = $edge['node'];
                $index++;
                if ($index >= $count) {
                    break 2;
                }
            }

            if (!$hasNextPage) {
                break;
            }

            if ($delayed) {
                // Random wait between 1 and 3 sec to mimic browser
                $microsec = rand($this->pagingDelayMinimumMicrosec, $this->pagingDelayMaximumMicrosec);
                usleep($microsec);
            }
        }
        $toReturn = [
            'hasNextPage' => $lastPagingInfo['has_next_page'],
            'nextPage' => $lastPagingInfo['end_cursor'],
            'accounts' => $accounts
        ];
        return $toReturn;
    }

    /**
     * @param $accountId
     * @param int $pageSize
     * @param string $nextPage
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getPaginateAllFollowing($accountId, $pageSize = 20, $nextPage = '')
    {
        $response = Request::get(Endpoints::getFollowingJsonLink($accountId, $pageSize, $nextPage),
            $this->generateHeaders($this->userSession));
        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Account with this id doesn\'t exist');
        }
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        $count = $jsonResponse['data']['user']['edge_follow']['count'];
        if ($count === 0) {
            return [];
        }

        $edgesArray = $jsonResponse['data']['user']['edge_follow']['edges'];
        if (count($edgesArray) === 0) {
            throw new InstagramException('Failed to get following of account id ' . $accountId . '. The account is private.', static::HTTP_FORBIDDEN);
        }

        $accounts = [];
        foreach ($edgesArray as $edge) {
            $accounts[] = $edge['node'];
        }

        $pageInfo = $jsonResponse['data']['user']['edge_follow']['page_info'];

        return [
            'count' => $count,
            'hasNextPage' => $pageInfo['has_next_page'],
            'nextPage' => $pageInfo['end_cursor'],
            'accounts' => $accounts
        ];
    }

    /**
     * Search users by followers
     * @param string $accountId Account id of the profile to query
     * @param string $query Query to search by followers
     *
     * @return array
     * @throws InstagramException
     */
    public function searchFollowers($accountId, $query = '')
    {
        $response = Request::get(
            Endpoints::getFollowersUrl_v1($accountId),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            ),
            array(
                "search_surface" => "follow_list_page",
                "query" => $query,
                "enable_groups" => "true"
            )
        );

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return $jsonResponse;
    }

    /**
     * Search users by following
     * @param string $accountId Account id of the profile to query
     * @param string $query Query to search by following
     *
     * @return array
     * @throws InstagramException
     */
    public function searchFollowing($accountId, $query = '')
    {
        $response = Request::get(
            Endpoints::getFollowingUrl_v1($accountId),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            ),
            array(
                "includes_hashtags" => "false",
                "search_surface" => "follow_list_page",
                "query" => $query,
                "enable_groups" => "true"
            )
        );

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return $jsonResponse;
    }

    /**
     * @param array $reel_ids - array of instagram user ids
     * @return array
     * @throws InstagramException
     */
    public function getStories($reel_ids = null, $highlight_reel_ids=[], $location_ids=[])
    {
        if (empty($reel_ids) )              $reel_ids           = [];
        elseif (!is_array($reel_ids))       $reel_ids           = [$reel_ids];
        if (!is_array($highlight_reel_ids)) $highlight_reel_ids = [$highlight_reel_ids];
        if (!is_array($location_ids))       $location_ids       = [$location_ids];
    //-------------------------------------------------------------------------------------------------------------
    // if no arguments: get user stories
    //-------------------------------------------------------------------------------------------------------------
        $variables = ['precomposed_overlay' => false, 'reel_ids' => []];
        if ($reel_ids == [] && $highlight_reel_ids == [] && $location_ids==[]) {

            $response = Request::get(Endpoints::getUserStoriesLink($variables),
                $this->generateHeaders($this->userSession));

            if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }

            $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

            if (empty($jsonResponse['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'])) {
                return [];
            }

            foreach ($jsonResponse['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'] as $edge) {
                $reel_ids[] = $edge['node']['id'];
            }
        }
    //-------------------------------------------------------------------------------------------------------------
    // Get stories from reel_ids (users), highlight_ids and/or location_ids
    //-------------------------------------------------------------------------------------------------------------
    $variables = [
             'highlight_reel_ids'=> $highlight_reel_ids,
             'reel_ids'=> $reel_ids,
             'location_ids'=> $location_ids,
             'precomposed_overlay'=> False,
      ];

      $url = Endpoints::HIGHLIGHT_STORIES . '&variables=' . json_encode($variables);
      $response = Request::get($url, $this->generateHeaders($this->userSession));

      if ($response->code !== static::HTTP_OK) {
          throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                       'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
      }

      $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

      if (empty($jsonResponse['data']['reels_media'])) {
          return [];
      }

      $stories = [];
      foreach ($jsonResponse['data']['reels_media'] as $highlight) {
          $UserStories = UserStories::create();
          $UserStories->setOwner(Account::create($highlight['owner']));
          foreach ($highlight['items'] as $item) {
              $UserStories->addStory(Story::create($item));
          }
          $stories[] = $UserStories;
      }
      return $stories;
  }


    /**
     * @param bool $force
     * @param bool|TwoStepVerificationInterface $twoStepVerificator
     *
     * $support_two_step_verification true works only in cli mode - just run login in cli mode - save cookie to file and use in any mode
     *
     * @return array
     * @throws InstagramAuthException
     * @throws InstagramChallengeRecaptchaException
     * @throws InstagramChallengeSubmitPhoneNumberException
     * @throws InstagramException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login($force = false, $twoStepVerificator = null)
    {
        if ($this->sessionUsername == null || $this->sessionPassword == null) {
            throw new InstagramAuthException("User credentials not provided");
        }

        if ($twoStepVerificator === true) {
            $twoStepVerificator = new ConsoleVerification();
        }

        $session = static::$instanceCache->get($this->getCacheKey());
        if ($force || !$this->isLoggedIn($session)) {
            $response = Request::get(Endpoints::BASE_URL);
            if ($response->code === static::HTTP_FOUND) {
                $response = Request::get($response->headers['Location'][0]);
            }
            if ($response->code !== static::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                             'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
            }
            preg_match('/\\\\"csrf_token\\\\":\\\\"(.*?)\\\\"/', $response->body, $match);
            $csrfToken = isset($match[1]) ? $match[1] : '';
            $cookies = $this->parseCookies($response->headers);

            $mid = array_key_exists('mid', $cookies) ? $cookies['mid'] : '';

            $cookieString = 'ig_cb=1';
            if ($csrfToken !== '') {
                $cookieString .= '; csrftoken=' . $csrfToken;
            }
            if ($mid !== '') {
                $cookieString .= '; mid=' . $mid;
            }

            $headers = [
                'cookie' => $cookieString,
                'referer' => Endpoints::BASE_URL . '/',
                'x-csrftoken' => $csrfToken,
                'X-CSRFToken' => $csrfToken,
                'user-agent' => $this->getUserAgent(),
            ];
            $response = Request::post(Endpoints::LOGIN_URL, $headers,
                ['username' => $this->sessionUsername, 'enc_password' => '#PWD_INSTAGRAM_BROWSER:0:' . time() . ':' . $this->sessionPassword]);

            if (isset($response->body->message) && $response->body->message == 'checkpoint_required') {
                $response = $this->verifyTwoStep($response, $cookies, $twoStepVerificator);
            }

            if ($response->code !== static::HTTP_OK && $response->code !== static::HTTP_FOUND) {
                if ((is_string($response->code) || is_numeric($response->code)) && is_string($response->body)) {
                    throw new InstagramAuthException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                                     'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
                } else {
                    throw new InstagramAuthException('Something went wrong. Please report issue.', $response->code);
                }
            }

            if (is_object($response->body) && !$response->body->authenticated) {
                throw new InstagramAuthException('User credentials are wrong.');
            }

            $cookies = $this->parseCookies($response->headers);

            $cookies['mid'] = $mid;
            static::$instanceCache->set($this->getCacheKey(), $cookies);
            $this->userSession = $cookies;
        } else {
            $this->userSession = $session;
        }

        return $this->generateHeaders($this->userSession);
    }

    /**
     * @param $sessionId
     *
     * @return array
     * @throws InstagramAuthException
     */
    public function loginWithSessionId($sessionId)
    {
        $sessionParts = explode('%',$sessionId);
        $session = ['sessionid' => $sessionId, 'ds_user_id' => $sessionParts[0], 'csrftoken' => md5( rand( 1, 5000 ) )];

        if (!$this->isLoggedIn($session)) {
            throw new InstagramAuthException('Login with session went wrong. Please report issue.');
        } else {
            $this->userSession = $session;
        }

        return $this->generateHeaders($this->userSession);
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return md5($this->sessionUsername);
    }

    /**
     * @param $session
     *
     * @return bool
     */
    public function isLoggedIn($session = null)
    {
        if ($session === null) {
            $session = static::$instanceCache->get($this->getCacheKey());
        }
        if (!isset($session['sessionid'])) {
            return false;
        }

        $sessionId = $session['sessionid'];
        $dsuserId = $session['ds_user_id'];
        $csrfToken = $session['csrftoken'];
        $headers = [
            'cookie' => "ig_cb=1; csrftoken=$csrfToken; sessionid=$sessionId; ds_user_id=$dsuserId",
            'referer' => Endpoints::BASE_URL . '/',
            'x-csrftoken' => $csrfToken,
            'X-CSRFToken' => $csrfToken,
            'user-agent' => $this->getUserAgent(),
        ];
        $response = Request::get(Endpoints::BASE_URL, $headers);
        if ($response->code !== static::HTTP_OK) {
            return false;
        }
        $cookies = $this->parseCookies($response->headers);
        return true;
    }

    /**
     * @param $response
     * @param $cookies
     * @param TwoStepVerificationInterface|null $twoStepVerificator
     *
     * @return Response
     * @throws InstagramAuthException
     * @throws InstagramChallengeRecaptchaException
     * @throws InstagramChallengeSubmitPhoneNumberException
     */
    private function verifyTwoStep($response, $cookies, TwoStepVerificationInterface $twoStepVerificator = null)
    {
        $new_cookies = $this->parseCookies($response->headers);
        $cookies = array_merge($cookies, $new_cookies);
        $cookie_string = '';
        foreach ($cookies as $name => $value) {
            $cookie_string .= $name . '=' . $value . '; ';
        }
        $headers = [
            'cookie' => $cookie_string,
            'referer' => Endpoints::LOGIN_URL,
            'x-csrftoken' => $cookies['csrftoken'],
            'user-agent' => $this->getUserAgent(),
        ];

        $url = Endpoints::BASE_URL . $response->body->checkpoint_url;
        $response = Request::get($url, $headers);

        if (preg_match('/"challengeType":"RecaptchaChallengeForm"/', $response->raw_body, $matches)) {
            throw new InstagramChallengeRecaptchaException('Instagram asked to enter the captcha.', $response->code);
        } elseif (preg_match('/"challengeType":"SubmitPhoneNumberForm"/', $response->raw_body, $matches)) {
            throw new InstagramChallengeSubmitPhoneNumberException('Instagram asked to enter a phone number.', $response->code);
        }

        // for 2FA case
        if (! $twoStepVerificator instanceof TwoStepVerificationInterface) {
            throw new InstagramAuthException('$twoStepVerificator must be an instance of TwoStepVerificationInterface.', $response->code);
        }

        if (preg_match('/window._sharedData\s\=\s(.*?)\;<\/script>/', $response->raw_body, $matches)) {
            $data = json_decode($matches[1], true, 512, JSON_BIGINT_AS_STRING);
            if (!empty($data['entry_data']['Challenge'][0]['extraData']['content'][3]['fields'][0]['values'])) {
                $choices = $data['entry_data']['Challenge'][0]['extraData']['content'][3]['fields'][0]['values'];
            } elseif (!empty($data['entry_data']['Challenge'][0]['fields'])) {
                $fields = $data['entry_data']['Challenge'][0]['fields'];
                if (!empty($fields['email'])) {
                    $choices[] = ['label' => 'Email: ' . $fields['email'], 'value' => 1];
                }
                if (!empty($fields['phone_number'])) {
                    $choices[] = ['label' => 'Phone: ' . $fields['phone_number'], 'value' => 0];
                }
            }

            if (!empty($choices)) {
                $selected_choice = $twoStepVerificator->getVerificationType($choices);
                $response = Request::post($url, $headers, ['choice' => $selected_choice]);
            }
        }

        if (!preg_match('/"input_name":"security_code"/', $response->raw_body, $matches)) {
            throw new InstagramAuthException('Something went wrong when try two step verification. Please report issue.', $response->code);
        }

        $security_code = $twoStepVerificator->getSecurityCode();

        $post_data = [
            'csrfmiddlewaretoken' => $cookies['csrftoken'],
            'verify' => 'Verify Account',
            'security_code' => $security_code,
        ];

        $response = Request::post($url, $headers, $post_data);

        if ($response->code !== static::HTTP_OK && $response->code !== static::HTTP_FOUND) {
            throw new InstagramAuthException('Something went wrong when try two step verification and enter security code. Please report issue.', $response->code);
        }

        return $response;
    }

    /**
     *
     */
    public function saveSession($ttl=null)
    {
        static::$instanceCache->set($this->getCacheKey(), $this->userSession, $ttl);
    }

    /**
     * @param int|string|Media $mediaId
     *
     * @return void
     * @throws InstagramException
     */
    public function like($mediaId)
    {
        $mediaId = $mediaId instanceof Media ? $mediaId->getId() : $mediaId;
        $response = Request::post(Endpoints::getLikeUrl($mediaId), $this->generateHeaders($this->userSession));

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }
    }

    /**
     * @param int|string|Media $mediaId
     *
     * @return void
     * @throws InstagramException
     */
    public function unlike($mediaId)
    {
        $mediaId = $mediaId instanceof Media ? $mediaId->getId() : $mediaId;
        $response = Request::post(Endpoints::getUnlikeUrl($mediaId), $this->generateHeaders($this->userSession));

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }
    }

    /**
     * @param string $accountId Account id of the profile to query
     *
     * @return void
     */
    public function follow($accountId){
        // return empty string and 302 code
        Request::post(Endpoints::getFollowUrl($accountId), $this->generateHeaders($this->userSession));
    }

    /**
     * @param string $accountId Account id of the profile to query
     *
     * @return void
     * @throws InstagramException
     */
    public function unfollow($accountId){
        $response = Request::post(Endpoints::getUnfollowUrl($accountId), $this->generateHeaders($this->userSession));

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }
    }

    /**
     * @param string $accountId Account id of the profile to query
     *
     * @return void
     * @throws InstagramException
     */
    public function removeFollower($accountId)
    {
        $response = Request::post(Endpoints::getRemoveFollowerUrl($accountId), $this->generateHeaders($this->userSession));

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }
    }

    /**
     * @return array
     * @throws InstagramException
     */
    public function pending()
    {
        $response = Request::get(
            Endpoints::getPendingUrl(),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            )
        );

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return $jsonResponse;
    }

    /**
     * @return array
     * @throws InstagramException
     */
    public function getInbox()
    {
        $response = Request::post(
            Endpoints::getInboxNewsUrl(),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            )
        );

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return $jsonResponse;
    }

    /**
     * @return bool
     * @throws InstagramException
     */
    public function seenInbox()
    {
        $response = Request::post(
            Endpoints::getInboxNewsSeenUrl(),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            )
        );

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return true;
    }

    /**
     * @param string $accountId Account id of the profile to query
     * @param int $count
     *
     * @return array
     * @throws InstagramException
     */
    public function getUserTags($accountId, $count = 12)
    {
        $response = Request::get(
            Endpoints::getUserTagsUrl($accountId, $count),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            )
        );
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return $jsonResponse;
    }

    /**
     * @param int|string|Media $mediaId
     * @param int|string $text
     * @param int|string|Comment|null $repliedToCommentId
     *
     * @return Comment
     * @throws InstagramException
     */
    public function addComment($mediaId, $text, $repliedToCommentId = null)
    {
        $mediaId = $mediaId instanceof Media ? $mediaId->getId() : $mediaId;
        $repliedToCommentId = $repliedToCommentId instanceof Comment ? $repliedToCommentId->getId() : $repliedToCommentId;

        $body = ['comment_text' => $text, 'replied_to_comment_id' => $repliedToCommentId];
        $response = Request::post(Endpoints::getAddCommentUrl($mediaId), $this->generateHeaders($this->userSession), $body);

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return Comment::create($jsonResponse);
    }

    /**
     * @param string|Media $mediaId
     * @param int|string|Comment $commentId
     * @return void
     * @throws InstagramException
     */
    public function deleteComment($mediaId, $commentId)
    {
        $mediaId = $mediaId instanceof Media ? $mediaId->getId() : $mediaId;
        $commentId = $commentId instanceof Comment ? $commentId->getId() : $commentId;
        $response = Request::post(Endpoints::getDeleteCommentUrl($mediaId, $commentId), $this->generateHeaders($this->userSession));

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . ' Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }
    }

    /**
     * @return null
     * @throws InstagramException
     */
    private function getRhxGis()
    {
        if ($this->rhxGis === null) {
            try {
                $sharedData = $this->getSharedDataFromPage();
                $this->rhxGis = $sharedData['rhx_gis'];
            } catch (Exception $exception) {
                throw new InstagramException('Could not extract gis from page');
            }
        }

        return $this->rhxGis;
    }

    /**
     * @param string $url
     * @return mixed|null
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    private function getSharedDataFromPage($url = Endpoints::BASE_URL)
    {
        $response = Request::get(rtrim($url, '/') . '/', $this->generateHeaders($this->userSession));
        if (static::HTTP_NOT_FOUND === $response->code) {
            throw new InstagramNotFoundException("Page {$url} not found");
        }

        if (static::HTTP_OK !== $response->code) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        return self::extractSharedDataFromBody($response->raw_body);
    }

    /**
     * @param string $userId
     *
     * @return Highlight[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getHighlights($userId)
    {
        $response = Request::get(Endpoints::getHighlightUrl($userId),
            $this->generateHeaders($this->userSession));

        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.', $response->code, static::getErrorBody($response->body));
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if (!isset($jsonResponse['status']) || $jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }

        if (empty($jsonResponse['data']['user']['edge_highlight_reels']['edges'])) {
            return [];
        }

        $highlights = [];
        foreach ($jsonResponse['data']['user']['edge_highlight_reels']['edges'] as $highlight_reel) {
            $highlights[] = Highlight::create($highlight_reel['node']);
        }
        return $highlights;
    }
    /**
     * @param array $highlight_reel_ids - array of instagram highlight ids
     * @return array
     * @throws InstagramException
     */
    public function getHighlightStories($highlight_reel_ids)
    {
      return $this->getStories([], $highlight_reel_ids, []);
    }

    /**
     * @param int $limit
     * @param int $messageLimit
     * @param string|null $cursor
     *
     * @return array
     * @throws InstagramException
     */
    public function getPaginateThreads($limit = 10, $messageLimit = 10, $cursor = null)
    {
        $response = Request::get(
            Endpoints::getThreadsUrl($limit, $messageLimit, $cursor),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            )
        );

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.');
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if (!isset($jsonResponse['status']) || $jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }

        if (!isset($jsonResponse['inbox']['threads']) || empty($jsonResponse['inbox']['threads'])) {
            return [];
        }

        $threads = [];

        foreach ($jsonResponse['inbox']['threads'] as $jsonThread) {
            $threads[] = Thread::create($jsonThread);
        }

        return [
            'hasOlder' => (bool) $jsonResponse['inbox']['has_older'],
            'oldestCursor' => isset($jsonResponse['inbox']['oldest_cursor']) ? $jsonResponse['inbox']['oldest_cursor'] : null,
            'threads' => $threads,
        ];
    }

    /**
     * @param int $count
     * @param int $limit
     * @param int $messageLimit
     *
     * @return Thread[]
     * @throws InstagramException
     */
    public function getThreads($count = 10, $limit = 10, $messageLimit = 10)
    {
        $this->allowPendingRequests();

        $threads = [];
        $cursor = null;

        while (count($threads) < $count) {
            $result = $this->getPaginateThreads($limit, $messageLimit, $cursor);

            if (!isset($result['threads'])) {
                break;
            }

            $threads = array_merge($threads, $result['threads']);

            if (!$result['hasOlder'] || !$result['oldestCursor']) {
                break;
            }

            $cursor = $result['oldestCursor'];
        }

        return $threads;
    }

    /**
     * @param int $limit
     *
     * @return void
     * @throws InstagramException
     */
    public function allowPendingRequests($limit = 10)
    {
        $response = Request::get(
            Endpoints::getThreadsPendingRequestsUrl($limit),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            )
        );

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.');
        }

        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if (!isset($jsonResponse['status']) || $jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response code is not equal 200. Something went wrong. Please report issue.');
        }

        if (!isset($jsonResponse['inbox']['threads']) || empty($jsonResponse['inbox']['threads'])) {
            return;
        }

        $threadIds = [];

        foreach ($jsonResponse['inbox']['threads'] as $thread) {
            $threadIds[] = $thread['thread_id'];
        }

        Request::post(
            Endpoints::getThreadsApproveMultipleUrl(),
            array_merge(
                ['x-ig-app-id' => self::X_IG_APP_ID],
                $this->generateHeaders($this->userSession)
            ),
            ['thread_ids' => json_encode($threadIds)]
        );
    }

    /**
     * @return Account
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getCurrentUserInfo(): Account
    {
        $response = Request::get(Endpoints::USER_FEED_hash, $this->generateHeaders($this->userSession));

        if ($response->code === static::HTTP_NOT_FOUND) {
            throw new InstagramNotFoundException('Account with given username does not exist.');
        }
        if ($response->code !== static::HTTP_OK) {
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.');
        }

        $this->parseCookies($response->headers);
        $jsonResponse = $this->decodeRawBodyToJson($response->raw_body);

        if(!isset($jsonResponse['data']['user']['username'])){
            throw new InstagramException('Response code is ' . $response->code . ': ' . static::httpCodeToString($response->code) . '.' .
                                         'Something went wrong. Please report issue.');
        }

        return $this->getAccount($jsonResponse['data']['user']['username']);
    }
}
