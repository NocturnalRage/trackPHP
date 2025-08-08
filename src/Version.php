<?php

namespace TrackPHP;

final class Version
{
    public const VERSION = self::readVersion();

    private static function readVersion(): string
    {
        $versionFile = __DIR__ . '/../VERSION';

        if (!file_exists($versionFile)) {
            throw new \RuntimeException('VERSION file is missing');
        }

        return trim(file_get_contents($versionFile));
    }
}
