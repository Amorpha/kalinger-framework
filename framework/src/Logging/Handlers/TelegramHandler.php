<?php
namespace Kalinger\Logging\Handlers;

use Kalinger\Debug;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class TelegramHandler extends AbstractProcessingHandler
{
    protected string $token;
    protected string $chatId;
    protected function smartEscapeMarkdown(string $text, bool $isKey = false): string
    {
        if ($isKey) {
            $specials = ['*', '`'];
        } else {
            $specials = ['*', '_', '`'];
        }

        foreach ($specials as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }

        return $text;
    }

    public function __construct(string $token, string $chatId, int|string $level = \Monolog\Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->token = $token;
        $this->chatId = $chatId;
    }

    protected function write(array $record): void
    {
        $text = $this->formatMessage($record);

        $this->send($text);
    }

    protected function formatMessage(array $record): string
    {
        $levelEmoji = match (strtoupper($record['level_name'])) {
            'EMERGENCY', 'ALERT', 'CRITICAL' => '🚨',
            'ERROR' => '❌',
            'WARNING' => '⚠️',
            'NOTICE' => '📣',
            'INFO' => 'ℹ️',
            'DEBUG' => '🐛',
            default => '📄',
        };

        $escapedMessage = $this->smartEscapeMarkdown($record['message'], false);
        $escapedDatetime = $record['datetime']->format('Y-m-d H:i:s');

        $context = $record['context'] ?? [];
        $debugBlock = '';

        if (!empty($context)) {
            $lines = [];
            foreach ($context as $key => $value) {
                $lines[] = sprintf(
                    "*%s*: `%s`",
                    $this->smartEscapeMarkdown((string)$key, true),
                    $this->smartEscapeMarkdown((string)$value, false)
                );
            }
            $debugBlock .= "\n\n*Context:*\n" . implode("\n", $lines);
        }

        return sprintf(
            "%s *%s*\n%s\n🕒 `%s`%s",
            $levelEmoji,
            strtoupper($record['level_name']),
            $escapedMessage,
            $escapedDatetime,
            $debugBlock
        );
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
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
    }
}
