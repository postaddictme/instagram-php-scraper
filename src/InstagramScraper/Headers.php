<?php


namespace InstagramScraper;


use InstagramScraper\Exception\InstagramException;
use Unirest\Request;

class Headers
{
    const UA_WEB = 'Mozilla/5.0 (Linux; Android 8.1.0; motorola one Build/OPKS28.63-18-3; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/70.0.3538.80 Mobile Safari/537.36 Instagram 72.0.0.21.98 Android (27/8.1.0; 320dpi; 720x1362; motorola; motorola one; deen_sprout; qcom; en_US; 132081645)';
    const UA_APP = 'Instagram 133.0.0.32.120 Android (24/7.0; 515dpi; 1440x2416; huawei/google; Nexus 6P; angler; angler; en-US; 204019456)';

    /** @var bool $emulateApp Should we mask ourselves as mobile app or just use website mode */
    public $emulateApp = false;

    private $userSession;
    private $userAgent;

    /** @var [] $additionalHeaders Added to request in any mode */
    private $additionalHeaders;

    private $defaultAppHeaders = [
        'X-IG-App-ID' => '', // int
        'X-IG-Capabilities' => '3brTvwM=',
        'X-IG-Connection-Type' => 'WIFI',
        'X-IG-App-Locale' => '', // en_US
        'X-IG-Device-Locale' => '', // en_US
        'X-IG-VP9-Capable' => false,
        'X-IG-Android-ID' => '', // string like `android-<hex-string>`
        'X-IG-Device-ID' => '', // uuid
        'X-FB-HTTP-Engine' => 'Liger',
        'X-IG-Connection-Speed' => '-1kbps',
        'X-IG-Bandwidth-Speed-KBPS' => '-1.000',
        'X-IG-Bandwidth-TotalBytes-B' => '0',
        'X-IG-Bandwidth-TotalTime-MS' => '0',
    ];
    /** @var [] $appHeaders Added to request in `emulateApp = true` mode */
    private $appHeaders;

    /**
     * @return array
     * @throws InstagramException
     */
    public function getLoginHeaders()
    {
        $session = $this->userSession;
        if ($session) {
            $sessionId = $session['sessionid'];
            $csrfToken = $session['csrftoken'];
            $cookieString = "ig_cb=1; csrftoken=$csrfToken; sessionid=$sessionId;";
        } else {
            $response = Request::get(Endpoints::BASE_URL);
            if ($response->code !== Instagram::HTTP_OK) {
                throw new InstagramException('Response code is ' . $response->code . '. Something went wrong. Please report issue.', $response->code);
            }
            preg_match('/"csrf_token":"(.*?)"/', $response->body, $match);
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
        }

        $headers = [
            'cookie' => $cookieString,
            'referer' => Endpoints::BASE_URL . '/',
            'x-csrftoken' => $csrfToken,
            'X-CSRFToken' => $csrfToken,
            'user-agent' => $this->getUserAgent(),
        ];

        if ($this->getAdditionalHeaders()) {
            $headers = array_merge($headers, $this->getAdditionalHeaders());
        }

        if ($this->emulateApp) {
            $headers = array_merge($headers, $this->getAppHeaders());
        }

        return $headers;
    }

    /**
     * @param $gisToken
     *
     * @return array
     */
    public function generateHeaders($gisToken = null)
    {
        $session = $this->userSession;

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

            if ($this->getAdditionalHeaders()) {
                $headers = array_merge($headers, $this->getAdditionalHeaders());
            }

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
     * We work only on https in this case if we have same cookies on Secure and not - we will choice Secure cookie
     *
     * @param array $headers
     *
     * @return array
     */
    public function parseCookies($headers)
    {
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
     *
     * @return string
     */
    public function getUserAgent()
    {
        if (!$this->userAgent) {
            if ($this->emulateApp) {
                return static::UA_APP;
            }
                return static::UA_WEB;
        }

        return $this->userAgent;
    }

    /**
     * @param $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @return null
     */
    public function resetUserAgent()
    {
        return $this->userAgent = null;
    }

    /**
     * Get additional headers
     *
     * @return string
     */
    public function getAdditionalHeaders()
    {
        return $this->additionalHeaders;
    }

    /**
     * @return []
     * @throws InstagramException
     */
    public function getAppHeaders()
    {
        if (!$this->appHeaders) {
            throw new InstagramException('You should set app headers, look into InstagramScraper\Headers::$defaultAppHeaders for example');
        }
        return $this->appHeaders;
    }

    /**
     * @param $headers
     * @return $this
     */
    public function setAppHeaders($headers)
    {
        $this->appHeaders = array_merge($this->defaultAppHeaders, $headers);

        return $this;
    }

    /**
     * Set additional headers to mimic mobile app
     *
     * @param [] $additionalHeaders
     * @return $this
     */
    public function setAdditionalHeaders($additionalHeaders)
    {
        $this->additionalHeaders = $additionalHeaders;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserSession()
    {
        return $this->userSession;
    }

    /**
     * @param $session
     * @return $this
     */
    public function setUserSession($session)
    {
        $this->userSession = $session;

        return $this;
    }

}