<?php
require_once __DIR__ . '/InstagramScraper/Instagram.php';
require_once __DIR__ . '/InstagramScraper/Endpoints.php';
require_once __DIR__ . '/InstagramScraper/InstagramQueryId.php';
require_once __DIR__ . '/InstagramScraper/Traits/ArrayLikeTrait.php';
require_once __DIR__ . '/InstagramScraper/Traits/InitializerTrait.php';
require_once __DIR__ . '/InstagramScraper/Model/AbstractModel.php';
require_once __DIR__ . '/InstagramScraper/Model/Account.php';
require_once __DIR__ . '/InstagramScraper/Model/CarouselMedia.php';
require_once __DIR__ . '/InstagramScraper/Model/Comment.php';
require_once __DIR__ . '/InstagramScraper/Model/Location.php';
require_once __DIR__ . '/InstagramScraper/Model/Media.php';
require_once __DIR__ . '/InstagramScraper/Model/Tag.php';
require_once __DIR__ . '/InstagramScraper/Exception/InstagramException.php';
require_once __DIR__ . '/InstagramScraper/Exception/InstagramAuthException.php';
require_once __DIR__ . '/InstagramScraper/Exception/InstagramNotFoundException.php';
