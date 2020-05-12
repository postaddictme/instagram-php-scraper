<?php

declare(strict_types=1);
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
     * @return string
     */
    public function getVerificationType(array $choices)
    {
        if (\count($choices) > 1) {
            $possible_values = [];
            echo 'Select where to send security code' . PHP_EOL;
            foreach ($choices as $choice) {
                echo $choice['label'] . ' - ' . $choice['value'] . PHP_EOL;
                $possible_values[$choice['value']] = true;
            }
            $selected_choice = null;
            while (empty($possible_values[$selected_choice])) {
                if ($selected_choice) {
                    echo 'Wrong choice. Try again' . PHP_EOL;
                }
                echo 'Your choice: ';
                $selected_choice = trim(fgets(STDIN));
            }
        } else {
            echo 'Message with security code sent to: ' . $choices[0]['label'] . PHP_EOL;
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
        while (\strlen($security_code) !== 6 && !\is_int($security_code)) {
            if ($security_code !== '') {
                echo 'Wrong security code' . PHP_EOL;
            }
            echo 'Enter security code: ';
            $security_code = trim(fgets(STDIN));
        }

        return $security_code;
    }
}
