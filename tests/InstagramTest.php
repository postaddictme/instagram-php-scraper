<?php

namespace InstagramScraper\Tests;

use InstagramScraper\Instagram;
use InstagramScraper\Model\Media;
use phpFastCache\CacheManager;
use PHPUnit\Framework\TestCase;

class InstagramTest extends TestCase
{
    /**
     * @var Instagram
     */
    private static $instagram;

    public static function setUpBeforeClass()
    {
        $sessionFolder = __DIR__ . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR;
        CacheManager::setDefaultConfig([
            'path' => $sessionFolder
        ]);
        $instanceCache = CacheManager::getInstance('files');
        self::$instagram = Instagram::withCredentials($_ENV['LOGIN'], $_ENV['PASSWORD'], $instanceCache);

        if (isset($_ENV['USER_AGENT'])) {
            self::$instagram->setUserAgent($_ENV['USER_AGENT']);
        }
        self::$instagram->login();

    }

    public function testGetAccountByUsername()
    {
        $account = self::$instagram->getAccount('kevin');
        $this->assertEquals('kevin', $account->getUsername());
        $this->assertEquals('3', $account->getId());
    }

    /**
     * @group getAccountById
     */
    public function testGetAccountById()
    {

        $account = self::$instagram->getAccountById(3);
        $this->assertEquals('kevin', $account->getUsername());
        $this->assertEquals('3', $account->getId());
    }

    public function testGetAccountByIdWithInvalidNumericId()
    {
        // PHP_INT_MAX is far larger than the greatest id so far and thus does not represent a valid account.
        $this->expectExceptionMessage('Failed to fetch account with given id');
        self::$instagram->getAccountById(PHP_INT_MAX);
    }

    public function testGetMedias()
    {
        $medias = self::$instagram->getMedias('kevin', 80);
        $this->assertEquals(80, sizeof($medias));
    }

    public function testGet100Medias()
    {
        $medias = self::$instagram->getMedias('kevin', 100);
        $this->assertEquals(100, sizeof($medias));
    }

    public function testGetMediasByTag()
    {
        $medias = self::$instagram->getMediasByTag('youneverknow', 20);
        $this->assertEquals(20, sizeof($medias));
    }

    public function testGetMediaByCode()
    {
        $media = self::$instagram->getMediaByCode('BHaRdodBouH');
        $this->assertEquals('kevin', $media->getOwner()->getUsername());
    }

    public function testGetMediaByUrl()
    {
        $media = self::$instagram->getMediaByUrl('https://www.instagram.com/p/BHaRdodBouH');
        $this->assertEquals('kevin', $media->getOwner()->getUsername());
    }

    public function testGetLocationTopMediasById()
    {
        $medias = self::$instagram->getCurrentTopMediasByTagName(1);
        $this->assertEquals(9, count($medias));
    }

    public function testGetLocationMediasById()
    {
        $medias = self::$instagram->getMediasByLocationId(1, 56);
        $this->assertEquals(56, count($medias));
    }

    public function testGetLocationById()
    {
        $location = self::$instagram->getLocationById(1);
        $this->assertEquals('Dog Patch Labs', $location->getName());
    }

    public function testGetMediaByTag()
    {
        $medias = self::$instagram->getMediasByTag('hello');
        echo json_encode($medias);
    }

    public function testGetIdFromCode()
    {
        $code = Media::getCodeFromId('1270593720437182847');
        $this->assertEquals('BGiDkHAgBF_', $code);
        $code = Media::getCodeFromId('1270593720437182847_3');
        $this->assertEquals('BGiDkHAgBF_', $code);
        $code = Media::getCodeFromId(1270593720437182847);
        $this->assertEquals('BGiDkHAgBF_', $code);
    }

    public function testGetCodeFromId()
    {
        $id = Media::getIdFromCode('BGiDkHAgBF_');
        $this->assertEquals(1270593720437182847, $id);
    }

    public function testGeMediaCommentsByCode()
    {
        $comments = self::$instagram->getMediaCommentsByCode('BR5Njq1gKmB', 40);
        //TODO: check why returns less comments
        $this->assertLessThanOrEqual(40, sizeof($comments));
    }

    /**
     * @group getUsernameById
     */
    public function testGetUsernameById()
    {
        $username = self::$instagram->getUsernameById(3);
        $this->assertEquals('kevin', $username);
    }

    /**
     * @group getMediasByIserId
     */
    public function testGetMediasByUserId()
    {
        $instagram = new Instagram();
        $nonPrivateAccountMedias = $instagram->getMediasByUserId(3);
        $this->assertEquals(12, count($nonPrivateAccountMedias));
    }

    public function testLikeMediaById()
    {
        // https://www.instagram.com/p/BcVEzBTgqKh/
        self::$instagram->like('1663256735663694497');
        $this->assertTrue(true, 'Return type ensures this assertion is never reached on failure');
    }

    public function testUnlikeMediaById()
    {
        // https://www.instagram.com/p/BcVEzBTgqKh/
        self::$instagram->unlike('1663256735663694497');
        $this->assertTrue(true, 'Return type ensures this assertion is never reached on failure');
    }

    public function testAddAndDeleteComment()
    {
        // https://www.instagram.com/p/BcVEzBTgqKh/
        $comment1 = self::$instagram->addComment('1663256735663694497', 'Cool!');
        $this->assertInstanceOf('InstagramScraper\Model\Comment', $comment1);

        $comment2 = self::$instagram->addComment('1663256735663694497', '+1', $comment1);
        $this->assertInstanceOf('InstagramScraper\Model\Comment', $comment2);

        self::$instagram->deleteComment('1663256735663694497', $comment2);
        $this->assertTrue(true, 'Return type ensures this assertion is never reached on failure');

        self::$instagram->deleteComment('1663256735663694497', $comment1);
        $this->assertTrue(true, 'Return type ensures this assertion is never reached on failure');
    }

    /**
     * @group getPaginateMediasByLocationId
     */
    public function testGetPaginateMediasByLocationId()
    {
        $medias = self::$instagram->getPaginateMediasByLocationId('201176299974017');
        echo json_encode($medias);
    }
    // TODO: Add test getMediaById
    // TODO: Add test getLocationById
}
