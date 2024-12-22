<?php

namespace AbdelrhmanSaeed\Route\API;


use AbdelrhmanSaeed\Route\Endpoints\Rest\{
    Rest,
    RestEndpoint,
    RestEndpointCollection
};

use AbdelrhmanSaeed\Route\Exceptions\{
    NotSupportedHttpMethodException,
    RequestIsHandledException,
    WrongRoutePatternException
};

use AbdelrhmanSaeed\Route\Resolvers\Resolver;
use Symfony\Component\HttpFoundation\{Request, Response};


/**
 *
 * @method static AbstractURI get(string $url, \Closure|array $action)
 * @method static AbstractURI post(string $url, \Closure|array $action)
 * @method static AbstractURI put(string $url, \Closure|array $action)
 * @method static AbstractURI patch(string $url, \Closure|array $action)
 * @method static AbstractURI delete(string $url, \Closure|array $action)
 */

class Route extends API
{
    /**
     * $headUri, $currentUri hold a reference to the current URI object
     * @var
     */
    private static ?Rest $headEndpoint = null , $currentEndpoint = null;

    /**
     * $supportedHttpMethods defines the supported https methods by the package
     * @var array
     */
    private static array $supportedHttpMethods = ['get', 'post', 'put', 'patch', 'delete'];



	public static function getHeadRestEndpoint(): ?Rest {
		return self::$headEndpoint;
	}

	public static function setHeadRestEndpoint(?Rest $headURI) {
		self::$headEndpoint = $headURI;
		return;
	}

	public static function getCurrentEndpoint(): ?Rest {
        return self::$currentEndpoint ?? self::$headEndpoint;
	}

	public static function setCurrentEndpoint(?Rest $currentEndpoint) {
		self::$currentEndpoint = $currentEndpoint;
		return;
	}
    public static function setMiddlewares(string ...$middlewares): RestEndpointCollection
    {
        return (new RestEndpointCollection())
                        ->setMiddlewares(...$middlewares);
    }

    public static function setController(string $controller): RestEndpointCollection
    {
        return new RestEndpointCollection(resolver: new Resolver($controller) );
    }

    private static function addRestEndpoint(Rest $endpoint): Rest
    {
        if ( ! is_null(self::$currentEndpoint) ) {
            return self::$currentEndpoint = self::$currentEndpoint->setNext($endpoint);
        }
        
        return self::$headEndpoint = self::$currentEndpoint = $endpoint;
    }

    /**
     * __callstatic used to make static method calls using
     * the supportedHttpMethods as the methods name instead of duplicating the same code for each method
     *
     * @param string $method
     * @param mixed $args
     * @throws \AbdelrhmanSaeed\Route\Exceptions\NotSupportedHttpMethodException
     * @return Rest
     */
    public static function __callstatic(string $method, mixed $args): Rest
    {
        /**
         * if the name of the called static method is not one of the supported http methods
         * a NotSupportedHttpMethodException exception will be thrown
         */
        if (! in_array(strtolower($method), self::$supportedHttpMethods)) {
            throw new NotSupportedHttpMethodException("the method '$method' is not supported!");
        }

        return self::addRestEndpoint(new RestEndpoint($args[0], [$method], new Resolver($args[1])));
    }

    public static function match(array $methods, string $route, \Closure|array $action): Rest
    {
        return self::addRestEndpoint(new RestEndpoint($route, $methods, new Resolver($action)));
    }

    public static function any(string $route, \Closure|array $action): RestEndpoint
    {
        return self::addRestEndpoint(new RestEndpoint($route, self::$supportedHttpMethods, new Resolver($action)));
    }

    /**
     * generate the route format for the resource endpoint
     * @param string $route
     * @param bool $shallow
     * @throws \AbdelrhmanSaeed\Route\Exceptions\WrongRoutePatternException
     * @return string[]
     */
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

    public static function resource(string $route, string $action, bool $api = true, bool $shallow = true): RestEndpointCollection
    {

        ($endpoint = new RestEndpoint(
                                ($preparedRoute = self::prepareResourceRoute($route, $shallow)) ['general'],
                                ['get'],
                                new Resolver([$action, 'index'])
                            )
                        )

                            ->setNext(
                                new RestEndpoint($preparedRoute['identified'], ['get'],
                                new Resolver([$action, 'show']) )
                            )

                            ->setNext(
                                new RestEndpoint($preparedRoute['general'],
                                ['post'],
                                new Resolver([$action, 'save']) )
                            )

                            ->setNext(
                                new RestEndpoint($preparedRoute['identified'], ['put', 'patch'],
                                new Resolver([$action, 'update']) )
                            )

                            ->setNext(
                                $tail = new RestEndpoint($preparedRoute['identified'], ['delete'],
                                new Resolver([$action, 'delete']) )
                            );

        if ( ! $api )
        {
            $tail
                ->setNext(
                    new RestEndpoint($preparedRoute['general'] . '/create', ['get'],
                    new Resolver([$action, 'create']) ))

                ->setNext(
                    new RestEndpoint($preparedRoute['identified'] . '/edit', ['get'],
                    new Resolver([$action, 'edit']) ));
        }

        return self::addRestEndpoint(new RestEndpointCollection($endpoint));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * 
     * @return void
     */
    public static function handle(Request $request, Response $response): void
    {
        if (is_null(self::$headEndpoint)) {
            return;
        }

        try { self::$headEndpoint?->handle($request); }
            catch(RequestIsHandledException $requestIsHandledException) { return; }

        if ( ! is_null(self::$actionOnNotFound))
        {
            (self::$actionOnNotFound) ();
            return;
        }

        echo $response->setStatusCode(404)->send();
    }
}
