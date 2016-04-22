<?php

/**
 * Created by PhpStorm.
 * User: rajymbekkapisev
 * Date: 20/04/16
 * Time: 01:17
 */
interface InstagramDataProvider
{
    function getAccount($username);
    function getMedias($username, $last = 10);

}