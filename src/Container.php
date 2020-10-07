<?php

declare(strict_types=1);

namespace tpr;

use ArrayAccess;
use Rakit\Validation\Validator;
use tpr\core\Config;
use tpr\core\Dispatch;
use tpr\core\Lang as CoreLang;
use tpr\core\request\RequestInterface;
use tpr\core\Response;
use tpr\exception\ClassNotExistException;
use tpr\exception\ContainerNotExistException;
use tpr\server\ServerInterface;

/**
 * Class Container.
 *
 * @method Config           config()    static
 * @method RequestInterface request()   static
 * @method Response         response()  static
 * @method ServerInterface  app()       static
 * @method CoreLang         lang()      static
 * @method Validator        validator() static
 */
final class Container implements ArrayAccess
{
    private static array $object = [];

    public static function __callStatic($name, $arguments)
    {
        if (!isset(self::$object[$name])) {
            throw new ContainerNotExistException($name);
        }

        return self::$object[$name];
    }

    /**
     * @return Dispatch|object
     */
    public static function dispatch(): ?object
    {
        return self::get('cgi_dispatch');
    }

    public static function bind(string $name, string $class_name, ...$params): void
    {
        if (!class_exists($class_name)) {
            throw new ClassNotExistException($name);
        }
        $object = new $class_name(...$params);
        self::bindWithObj($name, $object);
    }

    public static function bindWithObj(string $name, object $object): void
    {
        self::$object[$name] = $object;
    }

    public static function bindNX(string $name, string $class_name, array $params = []): void
    {
        if (!self::has($name)) {
            // Bind when not exist.
            self::bind($name, $class_name, $params);
        }
    }

    public static function bindNXWithObj(string $name, object $object): void
    {
        if (!self::has($name)) {
            // Bind when not exist.
            self::bindWithObj($name, $object);
        }
    }

    public static function import(array $classArray): void
    {
        foreach ($classArray as $key => $class) {
            self::bindNX($key, $class);
        }
    }

    public static function get(string $name): ?object
    {
        if (isset(self::$object[$name])) {
            return self::$object[$name];
        }

        return null;
    }

    public static function has(string $name): bool
    {
        return isset(self::$object[$name]);
    }

    public static function delete(string $name): void
    {
        if (isset(self::$object[$name])) {
            unset(self::$object[$name]);
        }
    }

    public function offsetExists($key): bool
    {
        return self::has($key);
    }

    public function offsetGet($key)
    {
        return self::get($key);
    }

    public function offsetSet($key, $value): void
    {
        if (\is_string($value)) {
            self::bind($key, $value);
        } else {
            self::bindWithObj($key, $value);
        }
    }

    public function offsetUnset($key): void
    {
        self::delete($key);
    }
}
