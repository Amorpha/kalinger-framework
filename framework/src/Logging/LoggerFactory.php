<?php
namespace Kalinger\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Kalinger\Logging\Handlers\TelegramHandler;

class LoggerFactory
{
    public static function create(string $name, array $config): Logger
    {
        $channelConfig = $config['channels'][$name] ?? throw new \InvalidArgumentException("Channel [$name] not defined");

        $logger = new Logger($name);

        $handlers = match ($channelConfig['driver']) {
            'single' => [new StreamHandler($channelConfig['path'], $channelConfig['level'])],
            'stderr' => [new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $channelConfig['level'])],
            'telegram' => [new TelegramHandler($channelConfig['token'], $channelConfig['chat_id'], $channelConfig['level'])],
            'stack' => self::stack($channelConfig['channels'], $config),
            default => throw new \InvalidArgumentException("Unsupported driver: {$channelConfig['driver']}"),
        };

        foreach ($handlers as $handler) {
            $logger->pushHandler($handler);
        }

        return $logger;
    }

    protected static function stack(array $channels, array $config): array
    {
        $handlers = [];

        foreach ($channels as $name) {
            $ch = $config['channels'][$name] ?? null;
            if (!$ch) continue;

            $handlers = [...$handlers, ...match ($ch['driver']) {
                'single' => [new StreamHandler($ch['path'], $ch['level'])],
                'stderr' => [new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $ch['level'])],
                'telegram' => [new TelegramHandler($ch['token'], $ch['chat_id'], $ch['level'])],
                default => [],
            }];
        }

        return $handlers;
    }
}
