<?php

use InstagramScraper\Model\Media;

require __DIR__ . '/../vendor/autoload.php';


echo 'CODE: ' . Media::getCodeFromId('1270593720437182847_3');
// OR
echo 'CODE: ' . Media::getCodeFromId('1270593720437182847');
// CODE: BGiDkHAgBF_
// So you can do like this: instagram.com/p/BGiDkHAgBF_


echo 'Media id: ' . Media::getIdFromCode('BGiDkHAgBF_');
// Media id: 1270593720437182847