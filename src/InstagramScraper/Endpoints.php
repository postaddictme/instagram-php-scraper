<?php

namespace InstagramScraper;

class Endpoints
{
    const BASE_URL = 'https://www.instagram.com';
    const ACCOUNT_PAGE = 'https://www.instagram.com/{username}';
    const MEDIA_LINK = 'https://www.instagram.com/p/{code}';
    const ACCOUNT_MEDIAS = 'https://www.instagram.com/{username}/media?max_id={max_id}';
    const ACCOUNT_JSON_INFO = 'https://www.instagram.com/{username}/?__a=1';
    const MEDIA_JSON_INFO = 'https://www.instagram.com/p/{code}/?__a=1';
    const MEDIA_JSON_BY_LOCATION_ID = 'https://www.instagram.com/explore/locations/{facebookLocationId}/?__a=1';
    const MEDIA_JSON_BY_TAG = 'https://www.instagram.com/explore/tags/{tag}/?__a=1&max_id={max_id}';
    const GENERAL_SEARCH = 'https://www.instagram.com/web/search/topsearch/?query={query}';
    const ACCOUNT_JSON_INFO_BY_ID = 'https://www.instagram.com/query/?q=ig_user({userId}){id,username,external_url,full_name,profile_pic_url,biography,followed_by{count},follows{count},media{count},is_private,is_verified}';
    

    public static function getAccountPageLink($username)
    {
        return str_replace('{username}', $username, Endpoints::ACCOUNT_PAGE);
    }

    public static function getAccountJsonLink($username)
    {
        return str_replace('{username}', $username, Endpoints::ACCOUNT_JSON_INFO);
    }

    public static function getAccountJsonInfoLinkByAccountId($id) {
        return str_replace('{userId}', $id, Endpoints::ACCOUNT_JSON_INFO_BY_ID);
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