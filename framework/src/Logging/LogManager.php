<?php
namespace Kalinger\Logging;

use Psr\Log\LoggerInterface;

class LogManager
{
    protected static ?self $instance = null;
    protected array $channels = [];
    protected array $config;

    private function __construct()
    {
        $this->config = require ROOT . '/config/logging.php';
    }

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    public function channel(string $name = null): LoggerInterface
    {
        $name ??= $this->config['default'];

        if (!isset($this->channels[$name])) {
            $this->channels[$name] = LoggerFactory::create($name, $this->config);
        }

        return $this->channels[$name];
    }

    public function __call(string $method, array $args)
    {
        return $this->channel()->$method(...$args);
    }

    public static function __callStatic(string $method, array $args)
    {
        return self::get()->channel()->$method(...$args);
    }
}
