<?php
require __DIR__ . '/../../vendor/autoload.php';

use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\TwoStepVerification\TwoStepVerificationInterface;
use SSilence\ImapClient\ImapClient as Imap;
use SSilence\ImapClient\ImapClientException;

/**
 * User: Aleksei S. Popov aka Lexotrion
 * Date: 2019-02-15
 * Time: 18:36
 *
 * Requires https://packagist.org/packages/php-mail-client/client
 */
class EmailVerification implements TwoStepVerificationInterface
{
    const MAIL_MAX_WAIT_TIME = 600;
    const MAIL_WAIT_STEP_TIME = 10;

    private $imapHost;
    private $email;
    private $username;
    private $pass;
    private $encryption;
    private $folder;

    /** @var Imap */
    private $imap;

    /**
     * EmailVerification constructor.
     * @param string $email
     * @param string $imapHost
     * @param string $pass
     * @param bool $encryption
     * @param string|null $username
     * @param string $folder
     * @throws InstagramAuthException
     */
    public function __construct($email, $imapHost, $pass, $encryption = true, $username = null, $folder = 'INBOX')
    {
        $this->email = $email;
        $this->imapHost = $imapHost;
        $this->pass = $pass;
        $this->encryption = $encryption ? Imap::ENCRYPT_SSL : null;
        $this->username = $username ?: $email;
        $this->folder = $folder;

        // Open connection
        try {
            $this->imap = new Imap($this->imapHost, $this->username, $this->pass, $this->encryption);
            // You can also check out example-connect.php for more connection options

        } catch (ImapClientException $error) {
            throw new InstagramAuthException('Login error. Cannot login to imap server: ' . $error->getMessage());
        }
    }


    /**
     * @param array $choices
     * @return string
     * @throws InstagramAuthException
     */
    public function getVerificationType(array $choices)
    {
        $maskedEmail = $this->getMaskedEmail();
        foreach ($choices as $choice) {
            if ($choice['label'] === 'Email: ' . $maskedEmail) {
                return $choice['value'];
            }
        }
        throw new InstagramAuthException('Login error. Two step verification via Email is not an option.');
    }

    private function getMaskedEmail()
    {
        $mail = explode('@', $this->email);
        $mail[0] = $mail[0][0]
            . \str_repeat('*', min(strlen($mail[0]) - 2,7))
            . $mail[0][strlen($mail[0]) - 1];
        $domain = explode('.', $mail[1]);
        $zone = array_pop($domain);
        $domain = $domain[0][0] . \str_repeat('*', strlen(implode('.', $domain)) - 1);
        return $mail[0] . '@' . $domain . '.' . $zone;
    }

    /**
     * @return string
     * @throws InstagramAuthException
     */
    public function getSecurityCode()
    {
        $this->imap->selectFolder($this->folder);
        $startTime = time();

        while (time() - $startTime < self::MAIL_MAX_WAIT_TIME) {
            sleep(mt_rand(60,self::MAIL_WAIT_STEP_TIME*60)/60);
            try {
                $code = $this->getCodeFromEmail();
            } catch (ImapClientException $e) {
                break;
            }
            if ($code) {
                return $code;
            }
            sleep(self::MAIL_WAIT_STEP_TIME);
        }
        throw new InstagramAuthException('Login error. Verification email read timeout.');
    }

    /**
     * @return bool
     * @throws ImapClientException
     */
    private function getCodeFromEmail()
    {
        $unreadMessages = $this->imap->countUnreadMessages();
        var_dump('countUnreadMessages: ' . $unreadMessages);
        $code = false;
        if ($unreadMessages) {
            $emails = $this->imap->getUnreadMessages();
            foreach ($emails as $mail) {
                if ($mail->header->from !== 'Instagram <security@mail.instagram.com>') {
                    continue;
                }
                $message = json_encode($mail->message);
                echo $message;
                if (preg_match('/>(\d{6})</', $message, $matches)) {
                    $code = $matches[1];
                }

            }
        }
        return $code;
    }
}