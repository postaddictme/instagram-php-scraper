<?php

namespace InstagramScraper;

interface InstagramDataProvider
{
    function getAccount($username);
    function getMedias($username, $last = 10);
}