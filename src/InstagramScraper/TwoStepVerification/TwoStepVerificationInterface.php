<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2019-02-15
 * Time: 18:27
 */

namespace InstagramScraper\TwoStepVerification;


interface TwoStepVerificationInterface
{

    /**
     * @param array $possible_values
     * @return string
     */
    public function getVerificationType(array $possible_values);

    /**
     * @return string
     */
    public function getSecurityCode();
}