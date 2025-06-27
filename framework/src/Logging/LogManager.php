<?php
namespace Kalinger\Logging;

use Kalinger\Logging\LoggerFactory;
use Monolog\Logger;

class LogManager
{
    protected static ?array $channels = null;

    public static function channel(string $name = 'default'): Logger
    {
        if (self::$channels === null) {
            $configPath = APPLICATION . '/config/logging.php';
            self::$channels = require $configPath;
        }

        return LoggerFactory::create($name, self::$channels);
    }

    public static function default(): Logger
    {
        return self::channel('default');
    }
}
