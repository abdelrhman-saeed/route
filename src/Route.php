<?php

namespace AbdelrhmanSaeed\Route;

use AbdelrhmanSaeed\Route\Exceptions\WrongRoutePatternException;
use AbdelrhmanSaeed\Route\URI\{URI, URIAction};
use AbdelrhmanSaeed\Route\URI\Constraints\{
    IURIConstraints,
    URIConstraints,
    URIConstraintsCollection
};
use AbdelrhmanSaeed\Route\Exceptions\NotSupportedHttpMethodException;
use Symfony\Component\HttpFoundation\{Request, Response};


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
    private static array $supportedHttpMethods = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * @var array $IUriConstraints {
     *  @type IUriConstraints
     * }
     */
    private static array $IURIConstraints;

    /**
     * the $actionOnNotFound Closure will be called if the request doesn't match any route
     * @var ?\Closure $actionOnNotFound
     */
    private static ?\Closure $actionOnNotFound = null;

    public static function notFound(\Closure $callback): void
    {
        self::$actionOnNotFound = $callback;
    }

    /**
     * instantiates and add a URI object to the URIs Stack
     * @param string $route
     * @param array $methods - http methods
     * @param \Closure|array $action
     * @return \AbdelrhmanSaeed\Route\URI\URI
     */
    private static function addURI(string $route, array $methods, \Closure|array $action): URI
    {
        // setting up an URI object and injecting a URIAction and URIConstraints objects to it
        ( $uri = new URI($route, $methods, new URIAction($action)) )
                        ->setUriConstraints(new URIConstraints($uri));

        // return $uri;
        /**
         * making a chain of URI objects apply the chain of responsibility design pattern
         * each URI object in the chain will try to handle the request
         * till one object handles it
         */
        if ( is_null(self::$currentUri) ) {
            return self::$headUri = self::$currentUri = $uri;
        }

        self::$currentUri
                ->setNext($uri);

        return self::$currentUri = $uri;
    }

    private static function addIURIConstraints(IURIConstraints $iURIConstraints): IURIConstraints
    {
        return self::$IURIConstraints[] = $iURIConstraints;
    }
    /**
     * __callstatic used to make static method calls using
     * the supportedHttpMethods as the methods name instead of duplicating the same code for each method
     * 
     * @param string $method
     * @param mixed $args
     * @throws \AbdelrhmanSaeed\Route\Exceptions\NotSupportedHttpMethodException
     * @return \AbdelrhmanSaeed\Route\URI\Constraints\URIConstraints
     */
    public static function __callstatic(string $method, mixed $args): IURIConstraints
    {
        /**
         * if the name of the called static method is not one of the supported http methods
         * a NotSupportedHttpMethodException exception will be thrown
         */
        if (! in_array(strtolower($method), self::$supportedHttpMethods)) {
            throw new NotSupportedHttpMethodException("the method '$method' is not supported!");
        }

        /**
         * returning the URIConstraints object that is linked the URI object
         * so the user be able to define some constraints on the route segments
         */
        return self::addIURIConstraints(
                self::addURI($args[0], [$method], $args[1])->getUriConstraints());

    }

    public static function match(array $methods, string $route, \Closure|array $action): IURIConstraints
    {
        return self::addIURIConstraints( self::addURI($route, $methods, $action)
                        ->getUriConstraints());
    }

    public static function any(string $route, \Closure|array $action): IURIConstraints
    {
        return self::addIURIConstraints( self::addURI($route, self::$supportedHttpMethods, $action)
                        ->getUriConstraints());
    }
    
    private static function prepareResourceRoute(string $route, bool $shallow): array
    {

        $preparedRoute = [];

        $preparedRoute['general']       = $route;
        $preparedRoute['identified']    = $preparedRoute['general'] . '/{' . $preparedRoute['general'] . '}';

        if (str_contains($route, '.'))
        {
            if (count($route = explode('.', $route)) > 2) {
                throw new WrongRoutePatternException("the right route pattern for a resource is 'example' or 'example.nestedexample'.");
            }

            $preparedRoute['general']       = "$route[0]/{" . $route[0] . "}/$route[1]";
            $preparedRoute['identified']    = $shallow ? $route[0] . '/' . $route[1] . '/{' . $route[1] . '}' : $preparedRoute['general'] . '/{' . $route[1] . '}';
        }

        return $preparedRoute;
    }

    public static function resource(string $route, string $action, bool $api = true, bool $shallow = true): IURIConstraints
    {

        $preparedRoute              = self::prepareResourceRoute($route, $shallow);
        $resourceRouteConstraints   = [];

        if (! $api)
        {
            $resourceRouteConstraints[] =
                self::addIURIConstraints(
                    self::addURI($preparedRoute['general'] . '/create', ['get'], [$action, 'create'])
                                            ->getUriConstraints());

            $resourceRouteConstraints[] =
                self::addIURIConstraints(
                    self::addURI($preparedRoute['identified'] . '/edit', ['get'], [$action, 'edit'])
                                            ->getUriConstraints());
        }

        $resourceRouteConstraints[] =
            self::addIURIConstraints(
                self::addURI($preparedRoute['general'], ['get'], [$action, 'index'])
                                        ->getUriConstraints());

        $resourceRouteConstraints[] =
            self::addIURIConstraints(
                self::addURI($preparedRoute['identified'], ['get'], [$action, 'show'])
                                        ->getUriConstraints());

        $resourceRouteConstraints[] =
            self::addIURIConstraints(
                self::addURI($preparedRoute['general'], ['post'], [$action, 'save'])
                                        ->getUriConstraints());

        $resourceRouteConstraints[] =
            self::addIURIConstraints(
                self::addURI($preparedRoute['identified'], ['put', 'patch'], [$action, 'update'])
                                        ->getUriConstraints());

        $resourceRouteConstraints[] =
            self::addIURIConstraints(
                self::addURI($preparedRoute['identified'], ['delete'], [$action, 'delete'])
                                        ->getUriConstraints());

        return new URIConstraintsCollection($resourceRouteConstraints);
    }

    private static function includeRoutes(string $path): void
    {
        if (is_file($path)) {
            require $path;
            return;
        }

        for($i = 2; $i < count ( $files = scandir($path) ); $i++) {
            require "$path/$files[$i]";
        }

    }
    public static function setup(string $routes, Request $request, Response $response): void
    {
        self::includeRoutes($routes);

        foreach(self::$IURIConstraints as $uriConstraint) {
            $uriConstraint->formatRouteToRegexPattern();
        }

        self::$headUri?->handle($request);

        if ( ! is_null(self::$actionOnNotFound))
        {
            (self::$actionOnNotFound) ();
            return;
        }

        echo $response->setStatusCode(404);
    }
}