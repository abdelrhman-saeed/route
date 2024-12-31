<?php

namespace AbdelrhmanSaeed\Route\API;

use Symfony\Component\HttpFoundation\{Request, Response};


abstract class API
{
    /**
     * @var ?callable $actionOnNotFound
     */
    protected static $actionOnNotFound = null;



    // abstract public static function setMiddlewares(string ...$middlewares): mixed;

    // abstract public static function setController(string $controller): mixed;

    protected static function includeEndpoints(string $path): void
    {
        if (is_file($path)) {
            require $path;
            return;
        }

        for($i = 2; $i < count ( $files = scandir($path) ); $i++) {
            self::includeEndpoints("$path/$files[$i]");
        }
    }

    /**
     * abstract method to let sub APIs handle the incoming request
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    protected abstract static function handle(Request $request, Response $response): void;

    /**
     * 
     * @param string $endpoints - path to the api endpoints file
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * 
     * @return void
     */

    public static function notFound(callable $callback): void {
        self::$actionOnNotFound = $callback;
    }

    public static function setup(string $endpoints, Request $request, Response $response): void
    {
        self::includeEndpoints($endpoints);
        static::handle($request, $response);
    }

}