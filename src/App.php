<?php

declare(strict_types=1);

namespace tpr;

use tpr\server\DefaultServer;
use tpr\server\ServerInterface;

/**
 * Class Client.
 *
 * @method DefaultServer default() static
 */
class App
{
    public static array $server_list = [
        'default' => DefaultServer::class,
    ];

    private static bool $debug = false;

    private static ?ServerInterface $handler = null;

    public static function __callStatic($name, $arguments): ServerInterface
    {
        return self::drive($name);
    }

    public static function debugMode($debug = null): bool
    {
        if (null === $debug) {
            return self::$debug;
        }
        if (!\is_bool($debug)) {
            throw new \InvalidArgumentException('debug param must be bool type');
        }
        self::$debug = $debug;

        return self::$debug;
    }

    public static function registerServer(string $name, string $class)
    {
        self::$server_list[$name] = $class;
    }

    public static function drive(string $name = null): ServerInterface
    {
        if (null === self::$handler) {
            if (!isset(self::$server_list[$name])) {
                throw new \InvalidArgumentException('Invalid server name : ' . $name .
                    ' (you can use `' . implode('/', array_keys(self::$server_list)) . '` for server name)');
            }
            Container::bind('app', self::$server_list[$name]);
            self::$handler = Container::app();
        }

        return self::$handler;
    }
}
