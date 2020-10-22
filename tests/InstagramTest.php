<?php

namespace InstagramScraper\Tests;

use GuzzleHttp\Client;
use InstagramScraper\Instagram;
use InstagramScraper\Model\Media;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Helper\Psr16Adapter;
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
        $defaultDriver = 'Files';
        $options = new ConfigurationOption([
            'path' => $sessionFolder
        ]);
        $instanceCache = new Psr16Adapter($defaultDriver, $options);

        self::$instagram = Instagram::withCredentials(new Client(), $_ENV['LOGIN'], $_ENV['PASSWORD'], $instanceCache);

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
        $this->expectException('InstagramScraper\Exception\InstagramNotFoundException');
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
        $medias = self::$instagram->getMediasByLocationId(881442298, 56);
        $this->assertEquals(56, count($medias));
    }

    public function testGetLocationById()
    {
        $location = self::$instagram->getLocationById(881442298);
        $this->assertEquals('Pendleberry Grove', $location->getName());
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
        $instagram = new Instagram(new Client());
        $nonPrivateAccountMedias = $instagram->getMediasByUserId(3);
        $this->assertEquals(12, count($nonPrivateAccountMedias));
    }

    public function testLikeMediaById()
    {
        // https://www.instagram.com/p/B910VxfgEIO/
        self::$instagram->like('2266948182120350222');
        $this->assertTrue(true, 'Return type ensures this assertion is never reached on failure');
    }

    public function testUnlikeMediaById()
    {
        // https://www.instagram.com/p/B910VxfgEIO/
        self::$instagram->unlike('2266948182120350222');
        $this->assertTrue(true, 'Return type ensures this assertion is never reached on failure');
    }

    public function testAddAndDeleteComment()
    {
        // https://www.instagram.com/p/B910VxfgEIO/
        $comment1 = self::$instagram->addComment('2266948182120350222', 'Cool!');
        $this->assertInstanceOf('InstagramScraper\Model\Comment', $comment1);

        $comment2 = self::$instagram->addComment('2266948182120350222', '+1', $comment1);
        $this->assertInstanceOf('InstagramScraper\Model\Comment', $comment2);

        self::$instagram->deleteComment('2266948182120350222', $comment2);
        $this->assertTrue(true, 'Return type ensures this assertion is never reached on failure');

        self::$instagram->deleteComment('2266948182120350222', $comment1);
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

    /**
     * @group getHighlights
     */
    public function testGetHighlights()
    {
        $userId = self::$instagram->getAccount('instagram')->getId();
        $highlights = self::$instagram->getHighlights($userId);
        $this->assertGreaterThan(0, sizeof($highlights));
    }

    // TODO: Add test getMediaById
    // TODO: Add test getLocationById
}
