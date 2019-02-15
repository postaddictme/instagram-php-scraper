<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2019-02-15
 * Time: 18:36
 */

namespace InstagramScraper\TwoStepVerification;


class ConsoleVerification implements TwoStepVerificationInterface
{

    /**
     * @param array $choices
     * @return string
     */
    public function getVerificationType(array $choices)
    {
        if (count($choices) > 1) {
            $possible_values = [];
            print 'Select where to send security code' .PHP_EOL;
            foreach ($choices as $choice) {
                print $choice['label'] . ' - ' . $choice['value'] .PHP_EOL;
                $possible_values[$choice['value']] = true;
            }
            $selected_choice = null;
            while (empty($possible_values[$selected_choice])) {
                if ($selected_choice) {
                    print 'Wrong choice. Try again'.PHP_EOL;
                }
                print 'Your choice: ';
                $selected_choice = trim(fgets(STDIN));
            }
        } else {
            print 'Message with security code sent to: ' . $choices[0]['label'] .PHP_EOL;
            $selected_choice = $choices[0]['value'];
        }
        return $selected_choice;
    }

    /**
     * @return string
     */
    public function getSecurityCode()
    {
        $security_code = '';
        while (strlen($security_code) !== 6 && !is_int($security_code)) {
            if ($security_code) {
                print 'Wrong security code'.PHP_EOL;
            }
            print 'Enter security code: ';
            $security_code = trim(fgets(STDIN));
        }
        return $security_code;
    }
}