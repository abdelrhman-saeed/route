<?php

namespace AbdelrhmanSaeed\Route;


use AbdelrhmanSaeed\Route\URI\{
    URI, AbstractURI, URICollection
};

use AbdelrhmanSaeed\Route\URI\Constraints\URIConstraints;
use AbdelrhmanSaeed\Route\URI\URIActions\URIAction;

use AbdelrhmanSaeed\Route\Exceptions\{
    NotSupportedHttpMethodException, RequestIsHandledException, WrongRoutePatternException
};

use Symfony\Component\HttpFoundation\{Request, Response};


/**
 * 
 * @method static AbstractURI get(string $url, \Closure|array $action)
 * @method static AbstractURI post(string $url, \Closure|array $action) 
 * @method static AbstractURI put(string $url, \Closure|array $action)
 * @method static AbstractURI patch(string $url, \Closure|array $action)
 * @method static AbstractURI delete(string $url, \Closure|array $action)
 */

class Route
{
    /**
     * $headUri, $currentUri hold a reference to the current URI object
     * @var 
     */
    private static ?AbstractURI $headURI = null , $currentURI = null;

    /**
     * $supportedHttpMethods defines the supported https methods by the package
     * @var array
     */
    private static array $supportedHttpMethods = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * the $actionOnNotFound Closure will be called if the request doesn't match any route
     * @var ?callable $actionOnNotFound
     */
    private static $actionOnNotFound = null;

    public static function notFound(callable $callback): void
    {
        self::$actionOnNotFound = $callback;
    }
	/**
	 * $headUri, $currentUri hold a reference to the current URI object
	 * @return 
	 */
	public static function getHeadURI(): ?AbstractURI {
		return self::$headURI;
	}
	
	/**
	 * $headUri, $currentUri hold a reference to the current URI object
	 * @param  $headURI $headUri, $currentUri hold a reference to the current URI object
	 */
	public static function setHeadURI(?AbstractURI $headURI) {
		self::$headURI = $headURI;
		return;
	}
	
	/**
	 * $headUri, $currentUri hold a reference to the current URI object
	 * @return 
	 */
	public static function getCurrentURI(): ?AbstractURI {
        return self::$currentURI ?? self::$headURI;
	}
	
	/**
	 * $headUri, $currentUri hold a reference to the current URI object
	 * @param  $currentURI $headUri, $currentUri hold a reference to the current URI object
	 */
	public static function setCurrentURI(?AbstractURI $currentURI) {
		self::$currentURI = $currentURI;
		return;
	}
    /**
     * instantiates and add a URI object to the URIs Stack
     * @param string $route
     * @param array $methods - http methods
     * @param \Closure|array $action
     * @return \AbdelrhmanSaeed\Route\URI\URI
     */
    private static function buildURI(string $route, array $methods, \Closure|string|array $action): URI
    {
        // setting up an URI object and injecting a URIAction and URIConstraints objects to it
        ( $uri = new URI($route, $methods, new URIAction($action)) )
                        ->setUriConstraints(new URIConstraints($uri));

        return $uri;
    }
    
    private static function addURI(AbstractURI $URI): AbstractURI
    {
        if ( is_null(self::$currentURI) ) {
            return self::$headURI = self::$currentURI = $URI;
        }

        self::$currentURI
                ->setNext($URI);

        return self::$currentURI = $URI;
    }

    /**
     * __callstatic used to make static method calls using
     * the supportedHttpMethods as the methods name instead of duplicating the same code for each method
     * 
     * @param string $method
     * @param mixed $args
     * @throws \AbdelrhmanSaeed\Route\Exceptions\NotSupportedHttpMethodException
     * @return \AbdelrhmanSaeed\Route\URI\AbstractURI
     */
    public static function __callstatic(string $method, mixed $args): AbstractURI
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
        
        return self::addURI(self::buildURI($args[0], [$method], $args[1]));
    }

    public static function match(array $methods, string $route, \Closure|array $action): AbstractURI
    {
        return self::addURI(self::buildURI($route, $methods, $action));
    }

    public static function any(string $route, \Closure|array $action): URI
    {
        return self::addURI(self::buildURI($route, self::$supportedHttpMethods, $action));
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

    public static function resource(string $route, string $action, bool $api = true, bool $shallow = true): URICollection
    {

        $preparedRoute  = self::prepareResourceRoute($route, $shallow);

        ($URI = self::buildURI($preparedRoute['general'], ['get'], [$action, 'index']))
                        ->setNext(self::buildURI($preparedRoute['identified'], ['get'], [$action, 'show']))
                        ->setNext(self::buildURI($preparedRoute['general'], ['post'], [$action, 'save']))
                        ->setNext(self::buildURI($preparedRoute['identified'], ['put', 'patch'], [$action, 'update']))
                        ->setNext($tail = self::buildURI($preparedRoute['identified'], ['delete'], [$action, 'delete']));

        if (! $api)
        {
            $tail->setNext(self::buildURI($preparedRoute['general'] . '/create', ['get'], [$action, 'create']))
                    ->setNext(self::buildURI($preparedRoute['identified'] . '/edit', ['get'], [$action, 'edit']));
        }

        self::addURI($URICollection = new URICollection($URI));

        return $URICollection;
    }

    public static function setMiddlewares(string ...$middlewares): URICollection
    {
        return (new URICollection())
                        ->setMiddlewares(...$middlewares);
    }

    public static function setController(string $controller): URICollection
    {
        return new URICollection( URIAction: new URIAction($controller) );
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
        
        if (is_null(self::$headURI)) {
            return;
        }

        try { self::$headURI?->handle($request); }
        
        catch(RequestIsHandledException $requestIsHandledException) {
            return;
        }

        if ( ! is_null(self::$actionOnNotFound))
        {
            (self::$actionOnNotFound) ();
            return;
        }

        echo $response->setStatusCode(404);

    }


}