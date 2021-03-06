<?php

declare(strict_types=1);

namespace tpr\exception;

use tpr\App;
use tpr\core\Response;
use tpr\exception\handler\DefaultHandler;
use tpr\exception\handler\JsonpHandler;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;

class Handler
{
    private static ?Run $run = null;

    private static array $handle_list = [
        'default' => DefaultHandler::class,
        'html'    => PrettyPageHandler::class,
        'text'    => PlainTextHandler::class,
        'json'    => JsonResponseHandler::class,
        'jsonp'   => JsonpHandler::class,
        'xml'     => XmlResponseHandler::class,
    ];

    private static string $handler_type = '';

    public static function init(): void
    {
        if (null === self::$run) {
            self::$run = new Run();
            self::$run->allowQuit();
        }
        if (\PHP_SAPI == 'cli') {
            self::$handler_type = App::drive()->getConfig()->default_content_type_cli;
        } elseif (!App::debugMode()) {
            self::$handler_type = 'default';
        } else {
            self::$handler_type = App::drive()->getConfig()->default_content_type_cgi;
        }
        self::addHandler(self::$handler_type);
        self::handleOperator()->register();
    }

    /**
     * @throws \Throwable
     */
    public static function render(\Throwable $exception, Response $response): void
    {
        self::selectOperator($response);

        throw $exception;
    }

    public static function selectOperator($response)
    {
        if (App::debugMode() && $response->getType() !== self::$handler_type) {
            self::$handler_type = $response->getType();
            if (isset(self::$handle_list[self::$handler_type])) {
                self::handleOperator()->clearHandlers();
                self::addHandler(self::$handler_type);
                self::handleOperator()->register();
            }
        }
    }

    public static function addHandler($handler): void
    {
        if (\is_string($handler)) {
            if (isset(self::$handle_list[$handler])) {
                $handler = self::$handle_list[$handler];
            }

            if (!class_exists($handler)) {
                throw new ClassNotExistException('Class Not Exist : ' . $handler);
            }

            $handler = new $handler();
        }

        if (\is_object($handler) && $handler instanceof HandlerInterface) {
            self::$run->appendHandler($handler);
        }
    }

    /**
     * @return Run
     */
    public static function handleOperator(): ?Run
    {
        return self::$run;
    }
}
