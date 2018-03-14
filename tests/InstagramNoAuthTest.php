<?php

require '../vendor/autoload.php';

use InstagramScraper\Instagram;
use InstagramScraper\Model\Media;
use phpFastCache\CacheManager;
use PHPUnit\Framework\TestCase;


class InstagramNoAuthTest extends TestCase
{
    

    /**
     * @group getMediasByIserId
     */
    public function testGetMediasByUserId()
    {
        $instagram = new Instagram();
        $nonPrivateAccountMedias = $instagram->getMediasByUserId(3);
        $this->assertEquals(20, count($nonPrivateAccountMedias));
    }

}