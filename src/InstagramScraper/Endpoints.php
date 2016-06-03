<?php

namespace InstagramScraper;

class Endpoints
{
    const BASE_URL = 'https://www.instagram.com';
    const ACCOUNT_PAGE = self::BASE_URL . '/{username}';
    const MEDIA_LINK = self::BASE_URL . '/p/{code}';

    const ACCOUNT_MEDIAS = self::ACCOUNT_PAGE . '/media?max_id={max_id}';
    const ACCOUNT_JSON_INFO = self::ACCOUNT_PAGE . '/?__a=1';
    const MEDIA_JSON_INFO = self::MEDIA_LINK . '/?__a=1';
    const MEDIA_JSON_BY_LOCATION_ID = self::BASE_URL . '/explore/locations/{facebookLocationId}/?__a=1';
    const MEDIA_JSON_BY_TAG = self::BASE_URL . '/explore/tags/{tag}/?__a=1&max_id={max_id}';

    const GENERAL_SEARCH = self::BASE_URL . '/web/search/topsearch/?query={query}';

    public static function getAccountPageLink($username)
    {
        return str_replace('{username}', $username, Endpoints::ACCOUNT_PAGE);
    }

    public static function getAccountJsonLink($username)
    {
        return str_replace('{username}', $username, Endpoints::ACCOUNT_JSON_INFO);
    }

    public static function getAccountMediasJsonLink($username, $maxId = '')
    {
        $url = str_replace('{username}', $username, Endpoints::ACCOUNT_MEDIAS);
        return str_replace('{max_id}', $maxId, $url);
    }

    public static function getMediaPageLink($code)
    {
        return str_replace('{code}', $code, Endpoints::MEDIA_LINK);
    }

    public static function getMediaJsonLink($code)
    {
        return str_replace('{code}', $code, Endpoints::MEDIA_JSON_INFO);
    }

    public static function getMediasJsonByLocationIdLink($facebookLocationId)
    {
        return str_replace('{facebookLocationId}', $facebookLocationId, Endpoints::MEDIA_JSON_BY_LOCATION_ID);
    }

    public static function getMediasJsonByTagLink($tag, $maxId = '')
    {
        $url = str_replace('{tag}', $tag, Endpoints::MEDIA_JSON_BY_TAG);
        return str_replace('{max_id}', $maxId, $url);
    }

    public static function getGeneralSearchJsonLink($query)
    {
        return str_replace('{query}', $query, Endpoints::GENERAL_SEARCH);
    }
}