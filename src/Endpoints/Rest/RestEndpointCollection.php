<?php

namespace AbdelrhmanSaeed\Route\Endpoints\Rest;

use AbdelrhmanSaeed\Route\API\Route;
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsInterface;
use AbdelrhmanSaeed\Route\Exceptions\WrongRoutePatternException;
use AbdelrhmanSaeed\Route\Resolvers\Resolver;
use Symfony\Component\HttpFoundation\Request;


class RestEndpointCollection extends Rest implements ConstraintsInterface
{
    public function __construct(protected ?Rest $endpoint = null, protected ?Resolver $resolver = null) {

    }

    /**
     * @method RestEndpointCollection do(\Closure $callback) - applys the callback to all the chained URIs
     * @param \Closure $callback
     * @return \AbdelrhmanSaeed\Route\Endpoints\Rest\RestEndpointCollection
     */
    private function do(\Closure $callback): RestEndpointCollection
    {
        $endpoint = $this->endpoint;

        do { $callback($endpoint); }
            while( ! is_null($endpoint = $endpoint->getNext()) );

        return $this;
    }

    public function setController(string $controller): self
    {
        $this->setResolver(new Resolver($controller));
        return $this;
    }

    public function setResolverToRestEndpoints(): RestEndpointCollection
    {

        if (is_null($this->resolver)) {
            return $this;
        }

        $controller = $this->resolver->getAction();

        $callback   =
            function (Rest $endpoint) use ($controller): void
                {
                    if ( ! is_string($endpoint->getResolver()->getAction())) {
                        throw new WrongRoutePatternException('routes nested in groups should only define methods as its action!');
                    }

                    $endpoint->getResolver()
                                ->setAction([$controller, $endpoint->getResolver()->getAction()]);
                };

        return $this->do($callback);
    }
    
    public function group(\Closure $callback): RestEndpointCollection
    {
        $headEndpoint       = Route::getHeadRestEndpoint();
        $currentEndpoint    = Route::getCurrentEndpoint();

        Route::setHeadRestEndpoint(null);
        Route::setCurrentEndpoint(null);
         
        $callback();

        $this->endpoint = Route::getHeadRestEndpoint();

        ! is_null($currentEndpoint)
            ? $currentEndpoint->setNext($this) : $headEndpoint = $currentEndpoint = $this;

        Route::setHeadRestEndpoint($headEndpoint);
        Route::setCurrentEndpoint($currentEndpoint);

        $middlewares = $this->getMiddlewares();

        $callback = function (RestEndpoint $endpoint) use ($middlewares) {
                        $endpoint->setMiddlewares(...$middlewares);
                    };

        $this->setResolverToRestEndpoints()
                ->do($callback); 

        return $this;
    }


    public function where(string $segment, string $regex): RestEndpointCollection
    {
        $callback = function (RestEndpoint $endpoint) use ($segment, $regex): void {
                        $endpoint->where($segment, $regex);
                    };

        return $this->do($callback);
    }
    
    /**
     * @inheritDoc
     */
    public function whereIn(string $segment, array $in): RestEndpointCollection
    {
        $callback = function (RestEndpoint $endpoint) use ($segment, $in): void {
                        $endpoint->whereIn($segment, $in);
                    };

        return $this->do($callback);
    }
    
    /**
     * @inheritDoc
     */
    public function whereOptional(array|string $regex): RestEndpointCollection
    {
        $callback = function (RestEndpoint $endpoint) use ($regex): void {
                        $endpoint->whereOptional($regex);
                    };

        return $this->do($callback);
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request): void
    {
        $this->endpoint->handle($request);

        /**
         * if any of the chained URIs above handled the request
         * an RequestHandlesException exception will be thrown
         * block the next line of getting executed
         */
        $this->next?->handle($request);
    }
}