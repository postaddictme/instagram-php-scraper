<?php

namespace InstagramScraper;


class Endpoints
{
    const BASE_URL = 'https://wwww.instagram.com';
    const USER_PAGE = self::BASE_URL . '/{username}';
    const MEDIA_LINK = self::BASE_URL . '/p/{code}';


    const USER_JSON_INFO = self::USER_PAGE . '/?__a=1';
    const MEDIA_JSON_INFO = self::MEDIA_LINK . '/?__a=1';
    const MEDIA_JSON_BY_LOCATION_ID = self::BASE_URL . '/explore/locations/{locationId}/?__a=1';
    const MEDIA_JSON_BY_TAG = self::BASE_URL . '/explore/tags/{tag}/?__a=1';

    const GENERAL_SEARCH = self::BASE_URL . '/web/search/topsearch/?query={key}';
}