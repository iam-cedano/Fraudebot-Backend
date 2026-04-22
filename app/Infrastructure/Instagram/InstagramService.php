<?php
namespace App\Infrastructure\Instagram;

class InstagramService implements InstagramServiceInterface
{
    public function getProfile($url)
    {
        return trim(parse_url($url, PHP_URL_PATH), '/');
    }
}