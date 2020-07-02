<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/EmailVerification.php';

/**
 * EmailVerification requires https://packagist.org/packages/php-mail-client/client
 */

$instagram = \InstagramScraper\Instagram::withCredentials('user', 'password');

$emailVecification = new EmailVerification(
    'user@mail.ru',
    'imap.mail.ru',
    'password'
);

$instagram->login(false, $emailVecification);

$account = $instagram->getAccountById(3);
echo $account->getUsername();