<?php

namespace InstagramScraper;

class Endpoints
{
    const BASE_URL = 'https://www.instagram.com';
    const LOGIN_URL = 'https://www.instagram.com/accounts/login/ajax/';
    const ACCOUNT_PAGE = 'https://www.instagram.com/{username}';
    const MEDIA_LINK = 'https://www.instagram.com/p/{code}';
    const ACCOUNT_MEDIAS = 'https://www.instagram.com/graphql/query/?query_hash=42323d64886122307be10013ad2dcc44&variables={variables}';
    const ACCOUNT_JSON_INFO = 'https://www.instagram.com/{username}/?__a=1';
    const MEDIA_JSON_INFO = 'https://www.instagram.com/p/{code}/?__a=1';
    const MEDIA_JSON_BY_LOCATION_ID = 'https://www.instagram.com/explore/locations/{{facebookLocationId}}/?__a=1&max_id={{maxId}}';
    const MEDIA_JSON_BY_TAG = 'https://www.instagram.com/explore/tags/{tag}/?__a=1&max_id={max_id}';
    const GENERAL_SEARCH = 'https://www.instagram.com/web/search/topsearch/?query={query}';
    const ACCOUNT_JSON_INFO_BY_ID = 'ig_user({userId}){id,username,external_url,full_name,profile_pic_url,biography,followed_by{count},follows{count},media{count},is_private,is_verified}';
    const COMMENTS_BEFORE_COMMENT_ID_BY_CODE = 'https://www.instagram.com/graphql/query/?query_id=17852405266163336&shortcode={{shortcode}}&first={{count}}&after={{commentId}}';
    const LAST_LIKES_BY_CODE = 'ig_shortcode({{code}}){likes{nodes{id,user{id,profile_pic_url,username,follows{count},followed_by{count},biography,full_name,media{count},is_private,external_url,is_verified}},page_info}}';
    const LIKES_BY_SHORTCODE = 'https://www.instagram.com/graphql/query/?query_id=17864450716183058&variables={"shortcode":"{{shortcode}}","first":{{count}},"after":"{{likeId}}"}';
    const FOLLOWING_URL = 'https://www.instagram.com/graphql/query/?query_id=17874545323001329&id={{accountId}}&first={{count}}&after={{after}}';
    const FOLLOWERS_URL = 'https://www.instagram.com/graphql/query/?query_id=17851374694183129&id={{accountId}}&first={{count}}&after={{after}}';
    const FOLLOW_URL = 'https://www.instagram.com/web/friendships/{{accountId}}/follow/';
    const UNFOLLOW_URL = 'https://www.instagram.com/web/friendships/{{accountId}}/unfollow/';
    const USER_FEED = 'https://www.instagram.com/graphql/query/?query_id=17861995474116400&fetch_media_item_count=12&fetch_media_item_cursor=&fetch_comment_count=4&fetch_like=10';
    const USER_FEED2 = 'https://www.instagram.com/?__a=1';
    const INSTAGRAM_QUERY_URL = 'https://www.instagram.com/query/';
    const INSTAGRAM_CDN_URL = 'https://scontent.cdninstagram.com/';
    const ACCOUNT_JSON_PRIVATE_INFO_BY_ID = 'https://i.instagram.com/api/v1/users/{userId}/info/';

    const ACCOUNT_MEDIAS2 = 'https://www.instagram.com/graphql/query/?query_id=17880160963012870&id={{accountId}}&first=10&after=';

    // Look alike??
    const URL_SIMILAR = 'https://www.instagram.com/graphql/query/?query_id=17845312237175864&id=4663052';

    const GRAPH_QL_QUERY_URL = 'https://www.instagram.com/graphql/query/?query_id={{queryId}}';

    private static $requestMediaCount = 30;

    /**
     * @param int $count
     */
    public static function setAccountMediasRequestCount($count)
    {
        static::$requestMediaCount = $count;
    }

    public static function getAccountMediasRequestCount()
    {
        return static::$requestMediaCount;
    }

    public static function getAccountPageLink($username)
    {
        return str_replace('{username}', urlencode($username), static::ACCOUNT_PAGE);
    }

    public static function getAccountJsonLink($username)
    {
        return str_replace('{username}', urlencode($username), static::ACCOUNT_JSON_INFO);
    }

    public static function getAccountJsonInfoLinkByAccountId($id)
    {
        return str_replace('{userId}', urlencode($id), static::ACCOUNT_JSON_INFO_BY_ID);
    }

    public static function getAccountJsonPrivateInfoLinkByAccountId($id)
    {
        return str_replace('{userId}', urlencode($id), static::ACCOUNT_JSON_PRIVATE_INFO_BY_ID);
    }

    public static function getAccountMediasJsonLink($variables)
    {
    	return str_replace('{variables}', urlencode($variables), static::ACCOUNT_MEDIAS);
    }

    public static function getMediaPageLink($code)
    {
        return str_replace('{code}', urlencode($code), static::MEDIA_LINK);
    }

    public static function getMediaJsonLink($code)
    {
        return str_replace('{code}', urlencode($code), static::MEDIA_JSON_INFO);
    }

    public static function getMediasJsonByLocationIdLink($facebookLocationId, $maxId = '')
    {
        $url = str_replace('{{facebookLocationId}}', urlencode($facebookLocationId), static::MEDIA_JSON_BY_LOCATION_ID);
        return str_replace('{{maxId}}', urlencode($maxId), $url);
    }

    public static function getMediasJsonByTagLink($tag, $maxId = '')
    {
        $url = str_replace('{tag}', urlencode($tag), static::MEDIA_JSON_BY_TAG);
        return str_replace('{max_id}', urlencode($maxId), $url);
    }

    public static function getGeneralSearchJsonLink($query)
    {
        return str_replace('{query}', urlencode($query), static::GENERAL_SEARCH);
    }

    public static function getCommentsBeforeCommentIdByCode($code, $count, $commentId)
    {
        $url = str_replace('{{shortcode}}', urlencode($code), static::COMMENTS_BEFORE_COMMENT_ID_BY_CODE);
        $url = str_replace('{{count}}', urlencode($count), $url);
        return str_replace('{{commentId}}', urlencode($commentId), $url);
    }

    public static function getLastLikesByCodeLink($code)
    {
        $url = str_replace('{{code}}', urlencode($code), static::LAST_LIKES_BY_CODE);
        return $url;
    }

    public static function getLastLikesByCode($code, $count, $lastLikeID)
    {
        $url = str_replace('{{shortcode}}', urlencode($code), static::LIKES_BY_SHORTCODE);
        $url = str_replace('{{count}}', urlencode($count), $url);
        $url = str_replace('{{likeId}}', urlencode($lastLikeID), $url);

        return $url;
    }

    public static function getGraphQlUrl($queryId, $parameters)
    {
        $url = str_replace('{{queryId}}', urlencode($queryId), static::GRAPH_QL_QUERY_URL);
        if (!empty($parameters)) {
            $query_string = http_build_query($parameters);
            $url .= '&' . $query_string;
        }
        return $url;
    }

    public static function getFollowUrl($accountId)
    {
        $url = str_replace('{{accountId}}', urlencode($accountId), static::FOLLOW_URL);
        return $url;
    }

    public static function getFollowersJsonLink($accountId, $count, $after = '')
    {
        $url = str_replace('{{accountId}}', urlencode($accountId), static::FOLLOWERS_URL);
        $url = str_replace('{{count}}', urlencode($count), $url);

        if ($after === '') {
            $url = str_replace('&after={{after}}', '', $url);
        } else {
            $url = str_replace('{{after}}', urlencode($after), $url);
        }

        return $url;
    }

    public static function getFollowingJsonLink($accountId, $count, $after = '')
    {
        $url = str_replace('{{accountId}}', urlencode($accountId), static::FOLLOWING_URL);
        $url = str_replace('{{count}}', urlencode($count), $url);

        if ($after === '') {
            $url = str_replace('&after={{after}}', '', $url);
        } else {
            $url = str_replace('{{after}}', urlencode($after), $url);
        }

        return $url;
    }

    public static function getUserStoriesLink()
    {
        $url = self::getGraphQlUrl(InstagramQueryId::USER_STORIES, ['variables' => json_encode([])]);
        return $url;
    }

    public static function getStoriesLink($variables)
    {
        $url = self::getGraphQlUrl(InstagramQueryId::STORIES, ['variables' => json_encode($variables)]);
        return $url;
    }
}
