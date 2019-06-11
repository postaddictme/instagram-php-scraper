<?php

namespace InstagramScraper;

use InstagramScraper\Exception\Instagram2FactorException;
use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Model\Account;
use InstagramScraper\Model\Comment;
use InstagramScraper\Model\Like;
use InstagramScraper\Model\Location;
use InstagramScraper\Model\Media;
use InstagramScraper\Model\Story;
use InstagramScraper\Model\Tag;
use InstagramScraper\Model\UserStories;
use InstagramScraper\TwoStepVerification\ConsoleVerification;
use InstagramScraper\TwoStepVerification\TwoStepVerificationInterface;
use InvalidArgumentException;
use phpFastCache\Cache\ExtendedCacheItemPoolInterface;
use phpFastCache\CacheManager;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

class Instagram
{
    const HTTP_NOT_FOUND = 404;
    const HTTP_OK = 200;
    const HTTP_FORBIDDEN = 403;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_TOO_MANY_REQUESTS = 429;

    const MAX_COMMENTS_PER_REQUEST = 300;
    const MAX_LIKES_PER_REQUEST = 300;
    const PAGING_TIME_LIMIT_SEC = 1800; // 30 mins time limit on operations that require multiple requests
    const PAGING_DELAY_MINIMUM_MICROSEC = 1000000; // 1 sec min delay to simulate browser
    const PAGING_DELAY_MAXIMUM_MICROSEC = 3000000; // 3 sec max delay to simulate browser

    const NETWORK_TIMEOUT = 10; // 10 seconds default timeout

    /** @var ExtendedCacheItemPoolInterface $instanceCache */
    private static $instanceCache;

    /**
     * @var array|null
     */
    private static $proxies;

    /**
     * Current position in proxies array
     * @var integer|null
     */
    private $currentProxy;

    /**
     * @var array
     */
    private $retries = [];

    /**
     * Auto retry request if connection failed.
     *
     * @var boolean
     */
    private static $autoRetry = false;

    /**
     * Number of retries before throwing exception.
     *
     * @var integer
     */
    private static $maxNbRetries = false;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;


    public $pagingTimeLimitSec = self::PAGING_TIME_LIMIT_SEC;
    public $pagingDelayMinimumMicrosec = self::PAGING_DELAY_MINIMUM_MICROSEC;
    public $pagingDelayMaximumMicrosec = self::PAGING_DELAY_MAXIMUM_MICROSEC;
    private $sessionUsername;
    private $sessionPassword;
    private $userSession;
    private $rhxGis = null;
    private $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36';

    /**
     * Instanciate Guzzle Client.
     */
    public function __construct()
    {
        $this->initClient();
    }

    /**
     * Instanciate Guzzle Client.
     *
     * @param  CookieJar|null $cookieJar
     * @return void
     */
    private function initClient(CookieJar $cookieJar = null)
    {
        $cookies = (null !== $cookieJar) ? $cookieJar : true;

        $this->client = new Client([
            'cookies' => $cookies,
            'timeout' => self::NETWORK_TIMEOUT,
            'headers' => [
                'User-Agent' => $this->getUserAgent(),
                'Accept' => '*/*',
                'Referer' => Endpoints::BASE_URL . '/',
            ],
        ]);
    }

    /**
     * Execute a HTTP Request.
     *
     * @param  string $method
     * @param  string $url
     * @param  array  $options
     * @param  string $notFoundMessage
     * @return string
     */
    private function makeRequest($method = 'GET', $url, array $options = [], $notFoundMessage = 'Resource not found.', $accessDeniedMessage = 'Forbidden access to ressource')
    {
        // Inject CSRF Token header based on cookie
        $cookie = $this->client->getConfig('cookies')->getCookieByName('csrftoken');
        if (null !== $cookie) {
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
            $options['headers']['x-csrftoken'] = $cookie->getValue();
        }

        $options = $this->defineProxyOption($options);

        try {
            $response = $this->client->request($method, $url, $options);
        }
        catch (ConnectException $e) {
            $this->markFailRequest();
            if ($this->canRetry()) {
                return $this->makeRequest($method, $url, $options, $notFoundMessage, $accessDeniedMessage);
            }
            throw $e;
        }
        catch (ClientException|RequestException $e) {
            if (null === $e->getResponse()) {
                $this->markFailRequest();
                if ($this->canRetry()) {
                    return $this->makeRequest($method, $url, $options, $notFoundMessage, $accessDeniedMessage);
                }
                throw new InstagramException('No Response fetched. Something went wrong. Please report issue.', $e->getCode(), $e);
            }
            if (static::HTTP_NOT_FOUND === $e->getResponse()->getStatusCode()) {
                throw new InstagramNotFoundException($notFoundMessage, $e->getResponse()->getStatusCode(), $e);
            }
            if (static::HTTP_FORBIDDEN === $e->getResponse()->getStatusCode()) {
                throw new InstagramAuthException($accessDeniedMessage, $e->getResponse()->getStatusCode(), $e);
            }
            if (static::HTTP_TOO_MANY_REQUESTS === $e->getResponse()->getStatusCode()) {
                $this->markFailRequest();
                if ($this->canRetry()) {
                    return $this->makeRequest($method, $url, $options, $notFoundMessage, $accessDeniedMessage);
                }
                throw new InstagramException('Too many Requests', $e->getResponse()->getStatusCode(), $e);
            }
            if (static::HTTP_BAD_REQUEST === $e->getResponse()->getStatusCode()) {
                // Try to decode response to check if it's a 2 factor response
                $data = json_decode($e->getResponse()->getBody()->getContents(), true);

                if (null !== $data && $data['two_factor_required'] === true) {
                    throw new Instagram2FactorException('2 Factor Authentication required', $e->getResponse()->getStatusCode(), $e);
                }
            }

            $this->markFailRequest();
            if ($this->canRetry()) {
                return $this->makeRequest($method, $url, $options, $notFoundMessage, $accessDeniedMessage);
            }
            throw new InstagramException('Response code is ' . $e->getResponse()->getStatusCode() . '. Something went wrong. Please report issue.', $e->getResponse()->getStatusCode(), $e);
        }

        $this->cleanFailedRequest();

        return $response->getBody()->getContents();
    }

    /**
     * Define Proxy options.
     *
     * @param  array  $options
     * @return array
     */
    private function defineProxyOption(array $options)
    {
        if (static::hasProxies()) {
            if (null === $this->currentProxy) {
                $this->currentProxy = 0;
            }
            $options['proxy'] = static::$proxies[$this->currentProxy];
        }

        return $options;
    }

    /**
     * @return boolean
     */
    private static function hasProxies()
    {
        return null !== static::$proxies && count(static::$proxies) > 0;
    }

    /**
     * Mark a fail request.
     *
     * @return void
     */
    private function markFailRequest()
    {
        if (static::hasProxies()) {
            if (!isset($this->retries[$this->currentProxy])) {
                $this->retries[$this->currentProxy] = 0;
            }
            $this->retries[$this->currentProxy]++;

            if (count(static::$proxies) > ($this->currentProxy + 1)) {
                $this->currentProxy++;
            } else {
                $this->currentProxy = 0;
            }
        } else {
            if (empty($this->retries)) {
                $this->retries = 1;
            } else {
                $this->retries++;
            }
        }
    }

    /**
     * Reset retry counters.
     *
     * @return void
     */
    private function cleanFailedRequest()
    {
        $this->retries = null;
    }

    /**
     * Tells if a request retry is possible.
     *
     * @return bool
     */
    private function canRetry()
    {
        if (static::$autoRetry === true) {
            if (static::hasProxies()) {
                foreach ($this->retries as $retry) {
                    if ($retry < static::$maxNbRetries) {
                        return true;
                    }
                }
                return false;
            } elseif ($this->retries < static::$maxNbRetries) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $username
     * @param string $password
     * @param null $sessionFolder
     *
     * @return Instagram
     * @throws \phpFastCache\Exceptions\phpFastCacheDriverCheckException
     */
    public static function withCredentials($username, $password, $sessionFolder = null)
    {
        if (is_null($sessionFolder)) {
            $sessionFolder = __DIR__ . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR;
        }
        if (is_string($sessionFolder)) {
            CacheManager::setDefaultConfig([
                'path' => $sessionFolder,
                'ignoreSymfonyNotice' => true,
            ]);
            static::$instanceCache = CacheManager::getInstance('files');
        } else {
            static::$instanceCache = $sessionFolder;
        }
        $instance = new self();
        $instance->sessionUsername = $username;
        $instance->sessionPassword = $password;
        return $instance;
    }

    /**
     * @param string $tag
     *
     * @return array
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public static function searchTagsByTagName($tag)
    {
        // TODO: Add tests and auth
        $response = $this->makeRequest('GET', Endpoints::getGeneralSearchJsonLink($tag), [], 'Account with given username does not exist.');

        $jsonResponse = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
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
     * @param \stdClass|string $rawError
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
                $str .= ' ' . $key . ' => ' . $value . ';';
            }
            return $str;
        } else {
            return 'Unknown body format';
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
     * Set custom curl opts
     */
    public static function curlOpts($opts)
    {
        Request::curlOpts($opts);
    }

    /**
     * @param bool $autoRetry
     */
    public static function setAutoRetry($autoRetry)
    {
        static::$autoRetry = $autoRetry;
    }

    /**
     * @param integer $autoRetry
     */
    public static function setMaxNbRetries($maxNbRetries)
    {
        static::$maxNbRetries = $maxNbRetries;
    }

    /**
     * @param array|string $config
     */
    public static function setProxy($config)
    {
        static::$proxies = [];
        static::$proxies = self::getProxyUri($config);
    }

    /**
     * @param array $proxies
     */
    public static function setProxies(array $proxies)
    {
        static::$proxies = [];

        if (count($proxies) > 0) {
            foreach ($proxies as $proxy) {
                static::$proxies[] = self::getProxyUri($proxy);
            }
        }
    }

    /**
     * Get an Proxy URI from a string (URI) or an array (config).
     *
     * @param  array|string $proxy
     * @return string
     */
    private static function getProxyUri($proxy)
    {
        if (is_array($proxy)) {
            return self::buildProxyURI($proxy);
        } elseif (is_scalar($proxy) && !empty($proxy)) {
            $uri = new Psr7\Uri($proxy);
            if (!Psr7\Uri::isAbsolute($uri)) {
                throw new \InvalidArgumentException($proxy.' is not a valid Proxy URI');
            }
            return $uri->__toString();
        }
    }

    /**
     * Build a proxy URI from a config array.
     *
     * @param  array  $config
     * @return string
     */
    private static function buildProxyURI(array $config)
    {
        // For BC
        $defaultConfig = [
            'port' => false,
            'tunnel' => false,
            'address' => false,
            'type' => 1,
            'timeout' => false,
            'auth' => [
                'user' => '',
                'pass' => '',
                'method' => 0
            ],
        ];

        $config = array_replace($defaultConfig, $config);

        $proxy = 'tcp://';
        if (!empty($config['auth']['user']) && !empty($config['auth']['pass'])) {
            $proxy .= $config['auth']['user'].':'.$config['auth']['pass'].'@';
        }
        $proxy .= $config['address'].':'.$config['port'];

        return $proxy;
    }

    /**
     * Disable proxy for all requests
     */
    public static function disableProxy()
    {
        static::$proxies = null;
    }

    /**
     * @param string $username
     *
     * @return Account[]
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function searchAccountsByUsername($username)
    {
        $response = $this->makeRequest('GET', Endpoints::getGeneralSearchJsonLink($username), [], 'Account with given username does not exist.');

        $jsonResponse = $this->decodeRawBodyToJson($response);

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

        $account = $this->getAccount($username);
        return $this->getMediasByUserId($account->getId(), $count, $maxId);
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
        $response = $this->makeRequest('GET', Endpoints::getAccountPageLink($username), [], 'Account with given username does not exist.');

        $userArray = self::extractSharedDataFromBody($response);

        if (!isset($userArray['entry_data']['ProfilePage'][0]['graphql']['user'])) {
            throw new InstagramNotFoundException('Account with this username does not exist');
        }
        return Account::create($userArray['entry_data']['ProfilePage'][0]['graphql']['user']);
    }

    private static function extractSharedDataFromBody($body)
    {
        if (preg_match_all('#\_sharedData \= (.*?)\;\<\/script\>#', $body, $out)) {
            return json_decode($out[1][0], true, 512, JSON_BIGINT_AS_STRING);
        }
        return null;
    }

    /**
     * @param int $id
     * @param int $count
     * @param string $maxId
     *
     * @return Media[]
     * @throws InstagramException
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

            $response = $this->makeRequest('GET', Endpoints::getAccountMediasJsonLink($variables), []);

            $arr = $this->decodeRawBodyToJson($response);

            if (!is_array($arr)) {
                throw new InstagramException('Something went wrong. Please report issue. Unable to decode response.', $response);
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
     * @return null
     * @throws InstagramException
     */
    private function getRhxGis()
    {
        if ($this->rhxGis === null) {
            try {
                $sharedData = $this->getSharedDataFromPage();
                $this->rhxGis = $sharedData['rhx_gis'];
            } catch (\Exception $exception) {
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
        $response = $this->makeRequest('GET', rtrim($url, '/') . '/', [], "Page {$url} not found");

        return self::extractSharedDataFromBody($response);
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
        $response = $this->makeRequest('GET', Endpoints::getAccountJsonLink($username), [], 'Account with given username does not exist.');

        $userArray = $this->decodeRawBodyToJson($response);

        if (!isset($userArray['graphql']['user'])) {
            throw new InstagramNotFoundException('Account with this username does not exist');
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
        $response = $this->makeRequest('GET', rtrim($mediaUrl, '/') . '/?__a=1', [], 'Media with given code does not exist or account is private.');

        $mediaArray = $this->decodeRawBodyToJson($response);
        if (!isset($mediaArray['graphql']['shortcode_media'])) {
            throw new InstagramException('Media with this code does not exist');
        }
        return Media::create($mediaArray['graphql']['shortcode_media']);
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
        $account = $this->getAccount($username);

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
     */
    public function getPaginateMediasByUserId($id, $count = 12, $maxId = '')
    {
        $hasNextPage = false;
        $medias = [];

        $toReturn = [
            'medias' => $medias,
            'maxId' => $maxId,
            'hasNextPage' => $hasNextPage,
        ];

        $variables = json_encode([
            'id' => (string)$id,
            'first' => (string)$count,
            'after' => (string)$maxId
        ]);

        $response = $this->makeRequest('GET', Endpoints::getAccountMediasJsonLink($variables));

        $arr = $this->decodeRawBodyToJson($response);

        if (!is_array($arr)) {
            throw new InstagramException('Something went wrong. Please report issue. Unable to decode response.', $response);
        }

        $nodes = $arr['data']['user']['edge_owner_to_timeline_media']['edges'];

        //if (count($arr['items']) === 0) {
        // I generally use empty. Im not sure why people would use count really - If the array is large then count takes longer/has more overhead.
        // If you simply need to know whether or not the array is empty then use empty.
        if (empty($nodes)) {
            return $toReturn;
        }

        foreach ($nodes as $mediaArray) {
            $medias[] = Media::create($mediaArray['node']);
        }

        $maxId = $arr['data']['user']['edge_owner_to_timeline_media']['page_info']['end_cursor'];
        $hasNextPage = $arr['data']['user']['edge_owner_to_timeline_media']['page_info']['has_next_page'];

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
     * @param      $code
     * @param int $count
     * @param null $maxId
     *
     * @return Comment[]
     * @throws InstagramException
     */
    public function getMediaCommentsByCode($code, $count = 10, $maxId = null)
    {
        $comments = [];
        $index = 0;
        $hasPrevious = true;
        while ($hasPrevious && $index < $count) {
            if (!isset($maxId)) {
                $maxId = '';
            }
            if ($count - $index > static::MAX_COMMENTS_PER_REQUEST) {
                $numberOfCommentsToRetreive = static::MAX_COMMENTS_PER_REQUEST;
            } else {
                $numberOfCommentsToRetreive = $count - $index;
            }

            $variables = json_encode([
                'shortcode' => (string)$code,
                'first' => (string)$numberOfCommentsToRetreive,
                'after' => (string)$maxId
            ]);

            $commentsUrl = Endpoints::getCommentsBeforeCommentIdByCode($variables);
            $response = $this->makeRequest('GET', $commentsUrl, []);
            $jsonResponse = $this->decodeRawBodyToJson($response);
            $nodes = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['edges'];
            foreach ($nodes as $commentArray) {
                $comments[] = Comment::create($commentArray['node']);
                $index++;
            }
            $hasPrevious = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['page_info']['has_next_page'];

            $numberOfComments = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['count'];
            if ($count > $numberOfComments) {
                $count = $numberOfComments;
            }
            if (sizeof($nodes) == 0) {
                return $comments;
            }
            $maxId = $jsonResponse['data']['shortcode_media']['edge_media_to_comment']['page_info']['end_cursor'];
        }
        return $comments;
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
            $response = $this->makeRequest('GET', $commentsUrl, []);

            $jsonResponse = $this->decodeRawBodyToJson($response);

            $nodes = $jsonResponse['data']['shortcode_media']['edge_liked_by']['edges'];

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
     * @return string
     * @throws InstagramException
     * @throws InstagramNotFoundException
     */
    public function getUsernameById($id)
    {
        $response = $this->makeRequest('GET', Endpoints::getAccountJsonPrivateInfoLinkByAccountId($id), [], 'Failed to fetch account with given id');

        if (!($responseArray = json_decode($response, true))) {
            throw new InstagramException('Response does not JSON');
        }

        if ($responseArray['status'] !== 'ok') {
            throw new InstagramException((isset($responseArray['message']) ? $responseArray['message'] : 'Unknown Error'));
        }

        return $responseArray['user']['username'];
    }

    /**
     * @param string $tag
     * @param int $count
     * @param string $maxId
     * @param string $minTimestamp
     *
     * @return Media[]
     * @throws InstagramException
     */
    public function getMediasByTag($tag, $count = 12, $maxId = '', $minTimestamp = null)
    {
        $index = 0;
        $medias = [];
        $mediaIds = [];
        $hasNextPage = true;
        while ($index < $count && $hasNextPage) {
            $response = $this->makeRequest('GET', Endpoints::getMediasJsonByTagLink($tag, $maxId), [], 'Failed to fetch account with given id');

            $arr = $this->decodeRawBodyToJson($response);

            if (!is_array($arr)) {
                throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
            }
            if (empty($arr['graphql']['hashtag']['edge_hashtag_to_media']['count'])) {
                return [];
            }

            $nodes = $arr['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
            foreach ($nodes as $mediaArray) {
                if ($index === $count) {
                    return $medias;
                }
                $media = Media::create($mediaArray['node']);
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
            $maxId = $arr['graphql']['hashtag']['edge_hashtag_to_media']['page_info']['end_cursor'];
            $hasNextPage = $arr['graphql']['hashtag']['edge_hashtag_to_media']['page_info']['has_next_page'];
        }
        return $medias;
    }

    /**
     * @param string $tag
     * @param string $maxId
     *
     * @return array
     * @throws InstagramException
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

        $response = $this->makeRequest('GET', Endpoints::getMediasJsonByTagLink($tag, $maxId), []);

        $arr = $this->decodeRawBodyToJson($response);

        if (!is_array($arr)) {
            throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
        }

        if (empty($arr['graphql']['hashtag']['edge_hashtag_to_media']['count'])) {
            return $toReturn;
        }

        $nodes = $arr['graphql']['hashtag']['edge_hashtag_to_media']['edges'];

        if (empty($nodes)) {
            return $toReturn;
        }

        foreach ($nodes as $mediaArray) {
            $medias[] = Media::create($mediaArray['node']);
        }

        $maxId = $arr['graphql']['hashtag']['edge_hashtag_to_media']['page_info']['end_cursor'];
        $hasNextPage = $arr['graphql']['hashtag']['edge_hashtag_to_media']['page_info']['has_next_page'];
        $count = $arr['graphql']['hashtag']['edge_hashtag_to_media']['count'];

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

        $response = $this->makeRequest('GET', Endpoints::getMediasJsonByLocationIdLink($facebookLocationId, $maxId), []);

        $arr = $this->decodeRawBodyToJson($response);

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

        foreach ($nodes as $mediaArray) {
            $medias[] = Media::create($mediaArray['node']);
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
        $response = $this->makeRequest('GET', Endpoints::getMediasJsonByTagLink($tagName, ''));

        $jsonResponse = $this->decodeRawBodyToJson($response);
        $medias = [];
        $nodes = (array)@$jsonResponse['graphql']['hashtag']['edge_hashtag_to_top_posts']['edges'];
        foreach ($nodes as $mediaArray) {
            $medias[] = Media::create($mediaArray['node']);
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
        $response = $this->makeRequest('GET', Endpoints::getMediasJsonByLocationIdLink($facebookLocationId), [], 'Location with this id doesn\'t exist');

        $jsonResponse = $this->decodeRawBodyToJson($response);
        $nodes = $jsonResponse['location']['top_posts']['nodes'];
        $medias = [];
        foreach ($nodes as $mediaArray) {
            $medias[] = Media::create($mediaArray);
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
     */
    public function getMediasByLocationId($facebookLocationId, $quantity = 24, $offset = '')
    {
        $index = 0;
        $medias = [];
        $hasNext = true;
        while ($index < $quantity && $hasNext) {
            $response = $this->makeRequest('GET', Endpoints::getMediasJsonByLocationIdLink($facebookLocationId, $offset));

            $arr = $this->decodeRawBodyToJson($response);
            $nodes = $arr['graphql']['location']['edge_location_to_media']['edges'];
            foreach ($nodes as $mediaArray) {
                if ($index === $quantity) {
                    return $medias;
                }
                $medias[] = Media::create($mediaArray['node']);
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
        $response = $this->makeRequest('GET', Endpoints::getMediasJsonByLocationIdLink($facebookLocationId));

        $jsonResponse = $this->decodeRawBodyToJson($response);
        return Location::create($jsonResponse['graphql']['location']);
    }

    /**
     * @param string $accountId Account id of the profile to query
     * @param int $count Total followers to retrieve
     * @param int $pageSize Internal page size for pagination
     * @param bool $delayed Use random delay between requests to mimic browser behaviour
     *
     * @return array
     * @throws InstagramException
     */
    public function getFollowers($accountId, $count = 20, $pageSize = 20, $delayed = true)
    {
        if ($delayed) {
            set_time_limit($this->pagingTimeLimitSec);
        }

        $index = 0;
        $accounts = [];
        $endCursor = '';

        if ($count < $pageSize) {
            throw new InstagramException('Count must be greater than or equal to page size.');
        }

        while (true) {
            $response = $this->makeRequest('GET', Endpoints::getFollowersJsonLink($accountId, $pageSize, $endCursor));

            $jsonResponse = $this->decodeRawBodyToJson($response);

            if ($jsonResponse['data']['user']['edge_followed_by']['count'] === 0) {
                return $accounts;
            }

            $edgesArray = $jsonResponse['data']['user']['edge_followed_by']['edges'];
            if (count($edgesArray) === 0) {
                throw new InstagramException('Failed to get followers of account id ' . $accountId . '. The account is private.', static::HTTP_FORBIDDEN);
            }

            foreach ($edgesArray as $edge) {
                $accounts[] = $edge['node'];
                $index++;
                if ($index >= $count) {
                    break 2;
                }
            }

            $pageInfo = $jsonResponse['data']['user']['edge_followed_by']['page_info'];
            if ($pageInfo['has_next_page']) {
                $endCursor = $pageInfo['end_cursor'];
            } else {
                break;
            }

            if ($delayed) {
                // Random wait between 1 and 3 sec to mimic browser
                $microsec = rand($this->pagingDelayMinimumMicrosec, $this->pagingDelayMaximumMicrosec);
                usleep($microsec);
            }
        }
        return $accounts;
    }

    /**
     * @param string $accountId Account id of the profile to query
     * @param int $count Total followed accounts to retrieve
     * @param int $pageSize Internal page size for pagination
     * @param bool $delayed Use random delay between requests to mimic browser behaviour
     *
     * @return array
     * @throws InstagramException
     */
    public function getFollowing($accountId, $count = 20, $pageSize = 20, $delayed = true)
    {
        if ($delayed) {
            set_time_limit($this->pagingTimeLimitSec);
        }

        $index = 0;
        $accounts = [];
        $endCursor = '';

        if ($count < $pageSize) {
            throw new InstagramException('Count must be greater than or equal to page size.');
        }

        while (true) {
            $response = $this->makeRequest('GET', Endpoints::getFollowingJsonLink($accountId, $pageSize, $endCursor));

            $jsonResponse = $this->decodeRawBodyToJson($response);

            if ($jsonResponse['data']['user']['edge_follow']['count'] === 0) {
                return $accounts;
            }

            $edgesArray = $jsonResponse['data']['user']['edge_follow']['edges'];
            if (count($edgesArray) === 0) {
                throw new InstagramException('Failed to get followers of account id ' . $accountId . '. The account is private.', static::HTTP_FORBIDDEN);
            }

            foreach ($edgesArray as $edge) {
                $accounts[] = $edge['node'];
                $index++;
                if ($index >= $count) {
                    break 2;
                }
            }

            $pageInfo = $jsonResponse['data']['user']['edge_follow']['page_info'];
            if ($pageInfo['has_next_page']) {
                $endCursor = $pageInfo['end_cursor'];
            } else {
                break;
            }

            if ($delayed) {
                // Random wait between 1 and 3 sec to mimic browser
                $microsec = rand($this->pagingDelayMinimumMicrosec, $this->pagingDelayMaximumMicrosec);
                usleep($microsec);
            }
        }
        return $accounts;
    }

    /**
     * @param array $reel_ids - array of instagram user ids
     * @return array
     * @throws InstagramException
     */
    public function getStories($reel_ids = null)
    {
        $variables = ['precomposed_overlay' => false, 'reel_ids' => []];
        if (empty($reel_ids)) {
            $response = $this->makeRequest('GET', Endpoints::getUserStoriesLink());

            $jsonResponse = $this->decodeRawBodyToJson($response);

            if (empty($jsonResponse['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'])) {
                return [];
            }

            foreach ($jsonResponse['data']['user']['feed_reels_tray']['edge_reels_tray_to_reel']['edges'] as $edge) {
                $variables['reel_ids'][] = $edge['node']['id'];
            }
        } else {
            $variables['reel_ids'] = $reel_ids;
        }

        $response = $this->makeRequest('GET', Endpoints::getStoriesLink($variables));

        $jsonResponse = $this->decodeRawBodyToJson($response);

        if (empty($jsonResponse['data']['reels_media'])) {
            return [];
        }

        $stories = [];
        foreach ($jsonResponse['data']['reels_media'] as $user) {
            $UserStories = UserStories::create();
            $UserStories->setOwner(Account::create($user['user']));
            foreach ($user['items'] as $item) {
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
     * @throws InstagramAuthException
     * @throws InstagramException
     *
     * @return array
     */
    public function login($force = false, $twoStepVerificator = null)
    {
        if ($this->sessionUsername == null || $this->sessionPassword == null) {
            throw new InstagramAuthException("User credentials not provided");
        }

        if ($twoStepVerificator === true) {
            $twoStepVerificator = new ConsoleVerification();
        }

        $cachedString = static::$instanceCache->getItem($this->sessionUsername);
        $session = $cachedString->get();
        if ($force || !$this->isLoggedIn($session)) {
            $response = $this->makeRequest('GET', Endpoints::BASE_URL);

            preg_match('/"csrf_token":"(.*?)"/', $response, $match);
            if (isset($match[1])) {
                $csrfToken = $match[1];
            }

            $headers = [
                'referer' => Endpoints::BASE_URL . '/',
                'x-csrftoken' => $csrfToken,
                'X-CSRFToken' => $csrfToken,
            ];

            try {
                $response = $this->makeRequest('POST', Endpoints::LOGIN_URL, [
                    'headers' => $headers,
                    'form_params' => [
                        'username' => $this->sessionUsername,
                        'password' => $this->sessionPassword
                    ],
                ], 'User credentials are wrong.');
                $jsonResponse = $this->decodeRawBodyToJson($response);

                if (!isset($jsonResponse['authenticated']) || $jsonResponse['authenticated'] !== true) {
                    throw new InstagramAuthException('User credentials are wrong.');
                }

                $cookies = $this->client->getConfig('cookies')->toArray();
                $cachedString->set($cookies);
                static::$instanceCache->save($cachedString);

            } catch (Instagram2FactorException $e) {
                // 2 factor not yet supported
                throw $e;
            }
        }
    }

    /**
     * @param array $session
     *
     * @return bool
     */
    public function isLoggedIn($session)
    {
        if (null === $session || !is_array($session)) {
            return false;
        }

        $this->initClient(new CookieJar(false, $session));

        try {
            $response = $this->makeRequest('GET', Endpoints::BASE_URL . '/');

            $cookie = $this->client->getConfig('cookies')->getCookieByName('ds_user_id');
            if (null === $cookie) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $response
     * @param $cookies
     * @param TwoStepVerificationInterface $twoStepVerificator
     * @return \Unirest\Response
     * @throws InstagramAuthException
     */
    private function verifyTwoStep($response, $cookies, $twoStepVerificator)
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

        if (!preg_match('/name="security_code"/', $response->raw_body, $matches)) {
            throw new InstagramAuthException('Something went wrong when try two step verification. Please report issue.', $response->code);
        }

        $security_code = $twoStepVerificator->getSecurityCode();

        $post_data = [
            'csrfmiddlewaretoken' => $cookies['csrftoken'],
            'verify' => 'Verify Account',
            'security_code' => $security_code,
        ];

        $response = Request::post($url, $headers, $post_data);

        if ($response->code !== static::HTTP_OK) {
            throw new InstagramAuthException('Something went wrong when try two step verification and enter security code. Please report issue.', $response->code);
        }

        return $response;
    }

    /**
     *
     */
    public function saveSession()
    {
        $cachedString = static::$instanceCache->getItem($this->sessionUsername);
        $cachedString->set($this->userSession);
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
        $response = $this->makeRequest('POST', Endpoints::getLikeUrl($mediaId));

        $jsonResponse = $this->decodeRawBodyToJson($response);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . '. Body: ' . static::getErrorBody($response) . ' Something went wrong. Please report issue.');
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
        $response = $this->makeRequest('POST', Endpoints::getUnlikeUrl($mediaId));

        $jsonResponse = $this->decodeRawBodyToJson($response);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . '. Body: ' . static::getErrorBody($response) . ' Something went wrong. Please report issue.');
        }
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
        $response = $this->makeRequest('POST', Endpoints::getAddCommentUrl($mediaId), [
            'form_params' => $body,
        ]);

        $jsonResponse = $this->decodeRawBodyToJson($response);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . '. Body: ' . static::getErrorBody($response) . ' Something went wrong. Please report issue.');
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
        $response = $this->makeRequest('POST', Endpoints::getDeleteCommentUrl($mediaId, $commentId));

        $jsonResponse = $this->decodeRawBodyToJson($response);

        if ($jsonResponse['status'] !== 'ok') {
            throw new InstagramException('Response status is ' . $jsonResponse['status'] . '. Body: ' . static::getErrorBody($response) . ' Something went wrong. Please report issue.');
        }
    }
}
