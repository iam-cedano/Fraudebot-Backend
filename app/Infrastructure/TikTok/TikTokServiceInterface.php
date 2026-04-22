<?php

namespace App\Infrastructure\TikTok;

interface TikTokServiceInterface
{
    public function getProfile($url);
}