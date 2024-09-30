<?php

namespace AbdelrhmanSaeed\Route;

use AbdelrhmanSaeed\Route\{
    URI\URI,
    URI\URIAction,
    URI\URIConstraints,
    Exceptions\NotSupportedHttpMethodException
};

use Symfony\Component\HttpFoundation\Request;


/**
 * 
 * @method static URIConstraints get(string $url, \Closure|array $action)
 * @method static URIConstraints post(string $url, \Closure|array $action) 
 * @method static URIConstraints put(string $url, \Closure|array $action)
 * @method static URIConstraints patch(string $url, \Closure|array $action)
 * @method static URIConstraints delete(string $url, \Closure|array $action)
 */

class Route
{
    private static ?URI $headUri , $currentUri = null;

    public static function __callstatic(string $method, mixed $args): URIConstraints
    {
        if (! in_array(strtolower($method), URI::$supportedHttpMethods)) {
            throw new NotSupportedHttpMethodException("the method '$method' is not supported!");
        }

        $uri = new URI(
                $args[0],
                $method,
                new URIAction($args[1])
            );

        $uri->setUriConstraints(
                new URIConstraints($uri));

        if (is_null(self::$currentUri))
        {
            self::$headUri = self::$currentUri = $uri;

            return $uri->getUriConstraints();
        }

        self::$currentUri->setNext($uri);
        self::$currentUri = $uri;

        return $uri->getUriConstraints();
    }

    public static function handle(): void
    {
        self::$headUri?->handle(Request::createFromGlobals());
    }
}