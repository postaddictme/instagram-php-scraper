<?php

namespace InstagramScraper\Http;

use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * Class Request
 * @package InstagramScraper\Http
 */
class Request
{
    /**
     * @var ClientInterface
     */
    private static $client;

    /**
     * @param ClientInterface $client
     */
    public static function setHttpClient(ClientInterface $client)
    {
        self::$client = $client;
    }

    /**
     * Send a cURL request
     * @param string $method HTTP method to use
     * @param string|Uri $uri URL to send the request to
     * @param mixed $body request body
     * @param array $headers additional headers to send
     *
     * @return Response
     * @throws ClientExceptionInterface
     */
    private static function send(string $method, $uri, array $headers = [], array $body = null)
    {
        if ($body !== null) {
            $body = http_build_query($body);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $body = Utils::streamFor($body);
        }
        $request = new Psr7Request($method, $uri, $headers, $body);

        return new Response(self::$client->sendRequest($request));
    }

    /**
     * Send a GET request to a URL
     *
     * @param string $url URL to send the GET request to
     * @param array $headers additional headers to send
     * @param array|null $parameters parameters to send in the querystring
     * @return Response
     * @throws ClientExceptionInterface
     */
    public static function get(string $url, array $headers = [], array $parameters = null)
    {
        $uri = new Uri($url);
        if ($parameters !== null) {
            $uri = $uri->withQuery(http_build_query($parameters));
        }
        return self::send('GET', $uri, $headers);
    }

    /**
     * @param string $url
     * @param array $headers
     * @param array|null $body
     * @return Response
     * @throws ClientExceptionInterface
     */
    public static function post(string $url, array $headers = [], array $body = null)
    {
        return self::send('POST', $url, $headers, $body);
    }
}