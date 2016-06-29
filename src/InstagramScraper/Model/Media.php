<?php

namespace InstagramScraper\Model;

use InstagramScraper\Endpoints;

class Media
{
    public $id;
    public $createdTime;
    public $type;
    public $link;
    public $imageLowResolutionUrl;
    public $imageThumbnailUrl;
    public $imageStandardResolutionUrl;
    public $imageHighResolutionUrl;
    public $caption;
    public $captionIsEdited;
    public $isAd;
    public $videoLowResolutionUrl;
    public $videoStandardResolutionUrl;
    public $videoLowBandwidthUrl;
    public $videoViews;
    public $code;
    public $owner;
    public $ownerId;
    public $likesCount;
    public $locationName;
    public $commentsCount;

    function __construct()
    {
    }

    public static function fromApi($mediaArray)
    {
        $instance = new self();
        $instance->id = $mediaArray['id'];
        $instance->type = $mediaArray['type'];
        $instance->createdTime = $mediaArray['created_time'];
        $instance->code = $mediaArray['code'];
        $instance->link = $mediaArray['link'];
        $instance->commentsCount = $mediaArray['comments']['count'];
        $instance->likesCount = $mediaArray['likes']['count'];
        $instance->imageLowResolutionUrl = self::getCleanImageUrl($mediaArray['images']['low_resolution']['url']);
        $instance->imageThumbnailUrl = self::getCleanImageUrl($mediaArray['images']['thumbnail']['url']);
        $instance->imageStandardResolutionUrl = self::getCleanImageUrl($mediaArray['images']['standard_resolution']['url']);
        $instance->imageHighResolutionUrl = str_replace('320x320', '1080x1080', $instance->imageLowResolutionUrl);
        if (isset($mediaArray['caption'])) {
            $instance->caption = $mediaArray['caption']['text'];
        }
        if ($instance->type === 'video') {
            $instance->videoViews = $mediaArray['video_views'];
            $instance->videoLowResolutionUrl = $mediaArray['videos']['low_resolution']['url'];
            $instance->videoStandardResolutionUrl = $mediaArray['videos']['standard_resolution']['url'];
            $instance->videoLowBandwidthUrl = $mediaArray['videos']['low_bandwidth']['url'];
        }
        if (isset($mediaArray['location'])) {
			$instance->locationName = $mediaArray['location']['name'];
		}
        return $instance;
    }

    private static function getCleanImageUrl($imageUrl)
    {
        return strpos($imageUrl, '?ig_cache_key=') ? substr($imageUrl, 0, strpos($imageUrl, '?ig_cache_key=')) : $imageUrl;
    }

    public static function fromMediaPage($mediaArray)
    {
        $instance = new self();
        $instance->id = $mediaArray['id'];
        $instance->type = 'image';
        if ($mediaArray['is_video']) {
            $instance->type = 'video';
            $instance->videoStandardResolutionUrl = $mediaArray['video_url'];
            $instance->videoViews = $mediaArray['video_views'];
        }
        if (isset($mediaArray['caption_is_edited'])) {
            $instance->captionIsEdited = $mediaArray['caption_is_edited'];
        }
        if (isset($mediaArray['is_ad'])) {
            $instance->isAd = $mediaArray['is_ad'];
        }
        $instance->createdTime = $mediaArray['date'];
        $instance->code = $mediaArray['code'];
        $instance->link = Endpoints::getMediaPageLink($instance->code);
        $instance->commentsCount = $mediaArray['comments']['count'];
        $instance->likesCount = $mediaArray['likes']['count'];
        $images = self::getImageUrls($mediaArray['display_src']);
        $instance->imageStandardResolutionUrl = $images['standard'];
        $instance->imageLowResolutionUrl = $images['low'];
        $instance->imageHighResolutionUrl = $images['high'];
        $instance->imageThumbnailUrl = $images['thumbnail'];
        if (isset($mediaArray['caption'])) {
            $instance->caption = $mediaArray['caption'];
        }
        if (isset($mediaArray['location'])) {
			$instance->locationName = $mediaArray['location']['name'];
		}
        $instance->owner = Account::fromMediaPage($mediaArray['owner']);
        return $instance;
    }

    private static function getImageUrls($imageUrl)
    {
        $imageUrl = self::getCleanImageUrl($imageUrl);
        $parts = explode('/', parse_url($imageUrl)['path']);
        if (sizeof($parts) == 4) {
            $standard = 'https://scontent.cdninstagram.com/' . $parts[1] . '/s640x640/' . $parts[2] . '/' . $parts[3];
        } else {
            if (isset($parts[4]) && $parts[4][0] == 'p') {
                $standard = 'https://scontent.cdninstagram.com/' . $parts[1] . '/p640x640/' . $parts[3] . '/' . $parts[5];
            } else {
                $standard = 'https://scontent.cdninstagram.com/' . $parts[1] . '/s640x640/' . $parts[3] . '/' . $parts[5];
            }
        }

        $urls = [
            'standard' => $standard,
            'low' => str_replace('640x640', '320x320', $standard),
            'high' => str_replace('640x640', '1080x1080', $standard),
            'thumbnail' => str_replace('640x640', '150x150', $standard)
        ];
        return $urls;
    }

    public static function fromTagPage($mediaArray)
    {
        $instance = new self();
        $instance->code = $mediaArray['code'];
        $instance->link = Endpoints::getMediaPageLink($instance->code);
        $instance->commentsCount = $mediaArray['comments']['count'];
        $instance->likesCount = $mediaArray['likes']['count'];
        $instance->ownerId = $mediaArray['owner']['id'];
        if (isset($mediaArray['caption'])) {
            $instance->caption = $mediaArray['caption'];
        }
        $instance->createdTime = $mediaArray['date'];
        $instance->imageThumbnailUrl = self::getCleanImageUrl($mediaArray['thumbnail_src']);
        $instance->imageStandardResolutionUrl = self::getCleanImageUrl($mediaArray['display_src']);
        $instance->type = 'image';
        if ($mediaArray['is_video']) {
            $instance->type = 'video';
            $instance->videoViews = $mediaArray['video_views'];
        }
        $instance->id = $mediaArray['id'];
        return $instance;
    }

    public static function getIdFromCode($code)
    {
        $alphabet = [
            '-' => 62, '1' => 53, '0' => 52, '3' => 55, '2' => 54, '5' => 57, '4' => 56, '7' => 59, '6' => 58, '9' => 61,
            '8' => 60, 'A' => 0, 'C' => 2, 'B' => 1, 'E' => 4, 'D' => 3, 'G' => 6, 'F' => 5, 'I' => 8, 'H' => 7,
            'K' => 10, 'J' => 9, 'M' => 12, 'L' => 11, 'O' => 14, 'N' => 13, 'Q' => 16, 'P' => 15, 'S' => 18, 'R' => 17,
            'U' => 20, 'T' => 19, 'W' => 22, 'V' => 21, 'Y' => 24, 'X' => 23, 'Z' => 25, '_' => 63, 'a' => 26, 'c' => 28,
            'b' => 27, 'e' => 30, 'd' => 29, 'g' => 32, 'f' => 31, 'i' => 34, 'h' => 33, 'k' => 36, 'j' => 35, 'm' => 38,
            'l' => 37, 'o' => 40, 'n' => 39, 'q' => 42, 'p' => 41, 's' => 44, 'r' => 43, 'u' => 46, 't' => 45, 'w' => 48,
            'v' => 47, 'y' => 50, 'x' => 49, 'z' => 51
        ];
        $n = 0;
        for ($i = 0; $i < strlen($code); $i++) {
            $c = $code[$i];
            $n = $n * 64 + $alphabet[$c];
        }
        return $n;
    }

    public static function getLinkFromId($id)
    {
        $code = Media::getCodeFromId($id);
        return Endpoints::getMediaPageLink($code);
    }

    public static function getCodeFromId($id)
    {
        $parts = explode('_', $id);
        $id = $parts[0];
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $shortenedId = '';
        while ($id > 0) {
            $remainder = $id % 64;
            $id = ($id - $remainder) / 64;
            $shortenedId = $alphabet{$remainder} . $shortenedId;
        };
        return $shortenedId;
    }
}
