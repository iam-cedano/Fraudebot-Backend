<?php

namespace App\Domain\ScammerProfile\ValueObjects;

use App\Domain\ScammerProfile\Enums\PlatformType;
use App\Infrastructure\Facebook\FacebookServiceInterface;
use App\Infrastructure\Instagram\InstagramServiceInterface;
use App\Infrastructure\TikTok\TikTokServiceInterface;
use App\Infrastructure\Youtube\YoutubeServiceInterface;

class Platform {
    public function __construct(private PlatformType $type) {}
    public function extractURL(string $url): string {
        return match($this->type) {
            PlatformType::FACEBOOK => app(FacebookServiceInterface::class)->getProfile($url),
            PlatformType::TIKTOK => app(TikTokServiceInterface::class)->getProfile($url),
            PlatformType::INSTAGRAM => app(InstagramServiceInterface::class)->getProfile($url),
            PlatformType::YOUTUBE => app(YoutubeServiceInterface::class)->getChannel($url),
            default => throw new \InvalidArgumentException('Unsupported PlatformType type'),
        };
    }
}