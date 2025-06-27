<?php
namespace Kalinger\Logging;

use Kalinger\Logging\Handlers\TelegramHandler;
use Monolog\Logger;

class CreateTelegramLogger
{
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('telegram');

        $handler = new TelegramHandler(
            $config['token'] ?? '',
            $config['chat_id'] ?? [],
            $config['level'] ?? Logger::WARNING
        );

        $logger->pushHandler($handler);

        return $logger;
    }
}
