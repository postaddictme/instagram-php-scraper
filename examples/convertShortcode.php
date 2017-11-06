<?php
require __DIR__ . '/../vendor/autoload.php';

use InstagramScraper\Model\Media;


echo 'Shortcode: ' . Media::getCodeFromId('1270593720437182847_3') . "\n"; // Shortcode: BGiDkHAgBF_
echo 'Shortcode: ' . Media::getCodeFromId('1270593720437182847') . "\n"; // Shortcode: BGiDkHAgBF_

// And you can get link to media: instagram.com/p/BGiDkHAgBF_

echo 'Media id: ' . Media::getIdFromCode('BGiDkHAgBF_'); // Media id: 1270593720437182847
