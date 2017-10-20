<?php

namespace InstagramScraper;

class Endpoints
{
    const BASE_URL = 'https://www.instagram.com';
    const LOGIN_URL = self:: BASE_URL . '/accounts/login/ajax/';
    const ACCOUNT_PAGE = self:: BASE_URL . '/{username}';
    const MEDIA_LINK = self:: BASE_URL . '/p/{code}';
    const ACCOUNT_MEDIAS = self:: BASE_URL . '/{username}/media/?max_id={max_id}';
    const ACCOUNT_JSON_INFO = self:: BASE_URL . '/{username}/?__a=1';
    const MEDIA_JSON_INFO = self:: BASE_URL . '/p/{code}/?__a=1';
    const MEDIA_JSON_BY_LOCATION_ID = self:: BASE_URL . '/explore/locations/{{facebookLocationId}}/?__a=1&max_id={{maxId}}';
    const MEDIA_JSON_BY_TAG = self:: BASE_URL . '/explore/tags/{tag}/?__a=1&max_id={max_id}';
    const GENERAL_SEARCH = self:: BASE_URL . '/web/search/topsearch/?query={query}';
    const ACCOUNT_JSON_INFO_BY_ID = 'ig_user({userId}){id,username,external_url,full_name,profile_pic_url,biography,followed_by{count},follows{count},media{count},is_private,is_verified}';
    const COMMENTS_BEFORE_COMMENT_ID_BY_CODE = self:: BASE_URL . '/graphql/query/?query_id=17852405266163336&shortcode={{shortcode}}&first={{count}}&after={{commentId}}';
    const LAST_LIKES_BY_CODE = 'ig_shortcode({{code}}){likes{nodes{id,user{id,profile_pic_url,username,follows{count},followed_by{count},biography,full_name,media{count},is_private,external_url,is_verified}},page_info}}';
    const FOLLOWING_URL = self:: BASE_URL . '/graphql/query/?query_id=17874545323001329&id={{accountId}}&first={{count}}';
    const FOLLOWERS_URL = self:: BASE_URL . '/graphql/query/?query_id=17851374694183129&id={{accountId}}&first={{count}}&after={{after}}';
    const FOLLOW_URL = self:: BASE_URL . '/web/friendships/{{accountId}}/follow/';
    const UNFOLLOW_URL = self:: BASE_URL . '/web/friendships/{{accountId}}/unfollow/';
    const USER_FEED = self:: BASE_URL . '/graphql/query/?query_id=17861995474116400&fetch_media_item_count=12&fetch_media_item_cursor=&fetch_comment_count=4&fetch_like=10';
    const USER_FEED2 = self:: BASE_URL . '/?__a=1';
    const INSTAGRAM_QUERY_URL = self:: BASE_URL . '/query/';
    const INSTAGRAM_CDN_URL = 'https://scontent.cdninstagram.com/';

    const ACCOUNT_MEDIAS2 = self:: BASE_URL . '/graphql/query/?query_id=17880160963012870&id={{accountId}}&first=10&after=';

    // Look alike??
    const URL_SIMILAR = self:: BASE_URL . '/graphql/query/?query_id=17845312237175864&id=4663052';

    const GRAPH_QL_QUERY_URL = self:: BASE_URL . '/graphql/query/?query_id={{queryId}}';


    public static function getAccountPageLink($username)
    {
        return str_replace('{username}', urlencode($username), Endpoints::ACCOUNT_PAGE);
    }

    public static function getAccountJsonLink($username)
    {
        return str_replace('{username}', urlencode($username), Endpoints::ACCOUNT_JSON_INFO);
    }

    public static function getAccountJsonInfoLinkByAccountId($id)
    {
        return str_replace('{userId}', urlencode($id), Endpoints::ACCOUNT_JSON_INFO_BY_ID);
    }

    public static function getAccountMediasJsonLink($username, $maxId = '')
    {
        $url = str_replace('{username}', urlencode($username), Endpoints::ACCOUNT_MEDIAS);
        return str_replace('{max_id}', urlencode($maxId), $url);
    }

    public static function getMediaPageLink($code)
    {
        return str_replace('{code}', urlencode($code), Endpoints::MEDIA_LINK);
    }

    public static function getMediaJsonLink($code)
    {
        return str_replace('{code}', urlencode($code), Endpoints::MEDIA_JSON_INFO);
    }

    public static function getMediasJsonByLocationIdLink($facebookLocationId, $maxId = '')
    {
        $url = str_replace('{{facebookLocationId}}', urlencode($facebookLocationId), Endpoints::MEDIA_JSON_BY_LOCATION_ID);
        return str_replace('{{maxId}}', urlencode($maxId), $url);
    }

    public static function getMediasJsonByTagLink($tag, $maxId = '')
    {
        $url = str_replace('{tag}', urlencode($tag), Endpoints::MEDIA_JSON_BY_TAG);
        return str_replace('{max_id}', urlencode($maxId), $url);
    }

    public static function getGeneralSearchJsonLink($query)
    {
        return str_replace('{query}', urlencode($query), Endpoints::GENERAL_SEARCH);
    }

    public static function getCommentsBeforeCommentIdByCode($code, $count, $commentId)
    {
        $url = str_replace('{{shortcode}}', urlencode($code), Endpoints::COMMENTS_BEFORE_COMMENT_ID_BY_CODE);
        $url = str_replace('{{count}}', urlencode($count), $url);
        return str_replace('{{commentId}}', urlencode($commentId), $url);
    }

    public static function getLastLikesByCodeLink($code)
    {
        $url = str_replace('{{code}}', urlencode($code), Endpoints::LAST_LIKES_BY_CODE);
        return $url;
    }

    public static function getGraphQlUrl($queryId, $parameters)
    {
        $url = str_replace('{{queryId}}', urlencode($queryId), Endpoints::GRAPH_QL_QUERY_URL);
        foreach ($parameters as $key => $value) {
            $url .= "&$key=$value";
        }
        return $url;
    }

    public static function getFollowUrl($accountId)
    {
        $url = str_replace('{{accountId}}', urlencode($accountId), Endpoints::FOLLOW_URL);
        return $url;
    }
    
    public static function getFollowersJsonLink($accountId, $count, $after = '')
    {
        $url = str_replace('{{accountId}}', urlencode($accountId), Endpoints::FOLLOWERS_URL);
        $url = str_replace('{{count}}', urlencode($count), $url);
        
        if ($after === '') {            
            $url = str_replace('&after={{after}}', '', $url);
        }
        else {
            $url = str_replace('{{after}}', urlencode($after), $url);
        }
        
        return $url;
    }
}
