<?php
namespace Kalinger\Logging\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class TelegramHandler extends AbstractProcessingHandler
{
    protected string $token;
    protected string $chatId;

    public function __construct(string $token, string $chatId, int|string $level = \Monolog\Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->token = $token;
        $this->chatId = $chatId;
    }

    protected function write(LogRecord $record): void
    {
        $text = sprintf(
            "*%s*\n%s\n`%s`",
            strtoupper($record->level->name),
            $record->message,
            $record->datetime->format('Y-m-d H:i:s')
        );

        $this->send($text);
    }

    protected function send(string $text): void
    {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $data = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT        => 4
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
