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
    /**
     * $headUri, $currentUri hold a reference to the current URI object
     * @var 
     */
    private static ?URI $headUri , $currentUri = null;

    /**
     * $supportedHttpMethods defines the supported https methods by the package
     * @var array
     */
    public static array $supportedHttpMethods = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * __callstatic used to make static method calls using
     * the supportedHttpMethods as the methods name instead of duplicating the same code for each method
     * 
     * @param string $method
     * @param mixed $args
     * @throws \AbdelrhmanSaeed\Route\Exceptions\NotSupportedHttpMethodException
     * @return \AbdelrhmanSaeed\Route\URI\URIConstraints
     */
    public static function __callstatic(string $method, mixed $args): URIConstraints
    {
        /**
         * if the name of the called static method is not one of the supported http methods
         * a NotSupportedHttpMethodException exception will be thrown
         */
        if (! in_array(strtolower($method), self::$supportedHttpMethods)) {
            throw new NotSupportedHttpMethodException("the method '$method' is not supported!");
        }

        // setting up an URI object and injecting a URIAction and URIConstraints objects to it
        $uri = new URI(
                $args[0],
                $method,
                new URIAction($args[1])
            );

        $uri->setUriConstraints(
                new URIConstraints($uri));

        /**
         * making a chaing of URI objects apply the chain of responsibility design pattern
         * each URI object in the chain will try to handle the request
         * till one object handles it
         */
        if (is_null(self::$currentUri))
        {
            self::$headUri = self::$currentUri = $uri;

            return $uri->getUriConstraints();
        }

        self::$currentUri->setNext($uri);
        self::$currentUri = $uri;

        /**
         * returning the URIConstraints object that is linked the URI object
         * so the user be able to define some constraints on the route segments
         */
        return $uri->getUriConstraints();
    }

    public static function setup(string $routesFile): void
    {
        require $routesFile;
        self::$headUri?->handle(Request::createFromGlobals());
    }

    // public static function resource(
    //     string $url, string $controller, bool $nested = false, bool $shallowed = false): void
    // {
        

        
    //     $identifiedResourceUrl = "$url/{$url}";

    //     /**
    //      * get method
    //      */
    //     self::get($url, [$controller, 'index']);
    //     self::get($identifiedResourceUrl, [$controller, 'show']);

    //     /**
    //      * post method
    //      */
    //     self::post($url, [$controller, 'save']);

    //     /**
    //      * put
    //      */
    //     self::put($identifiedResourceUrl, [$controller, 'update']);

    //     /**
    //      * patch
    //      */
    //     self::patch($identifiedResourceUrl, [$controller, 'update']);

    //     /**
    //      * delete
    //      */
    //     self::delete($identifiedResourceUrl, [$controller, 'delete']);
    // }
}