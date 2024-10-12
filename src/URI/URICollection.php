<?php

namespace AbdelrhmanSaeed\Route\URI;

use AbdelrhmanSaeed\Route\Exceptions\WrongRoutePatternException;
use AbdelrhmanSaeed\Route\Route;
use AbdelrhmanSaeed\Route\URI\Constraints\URIConstraintsInterface;
use AbdelrhmanSaeed\Route\URI\URIActions\URIAction;
use Symfony\Component\HttpFoundation\Request;


class URICollection extends AbstractURI implements URIConstraintsInterface
{
    
    public function __construct(protected ?AbstractURI $URI = null, protected ?URIAction $URIAction = null) {

    }

    public function handle(Request $request): void
    {
        $this->URI->handle($request);

        /**
         * if any of the chained URIs above handled the request
         * an RequestHandlesException exception will be thrown
         * block the next line of getting executed
         */
        $this->next?->handle($request);
    }

    public function setControllerToURIs(): URICollection
    {
        if (is_null($this->URIAction)) {
            return $this;
        }

        $controller = $this->URIAction->getAction();

        $callback   =
            function (URI $URI) use ($controller)
                {
                    if ( ! is_string($URI->getURIAction()->getAction())) {
                        throw new WrongRoutePatternException('routes nested in groups should only define methods as its action!');
                    }

                    $URI->getURIAction()
                        ->setAction([$controller, $URI->getURIAction()->getAction()]);
                };

        return $this->do($callback);
    }
    
    public function group(\Closure $callback): URICollection
    {
        $headURI    = Route::getHeadURI();
        $currentURI = Route::getCurrentURI();

        Route::setHeadURI(null);
        Route::setCurrentURI(null);
         
        $callback();

        $this->URI = Route::getHeadURI();

        ! is_null($headURI)
            ? $headURI->setNext($this) : ($headURI = $currentURI = $this);

        Route::setHeadURI($headURI);
        Route::setCurrentURI($currentURI);

        $middlewares = $this->getMiddlewares();

        $callback = function (URI $URI) use ($middlewares) {
                        $URI->setMiddlewares(...$middlewares);
                    };

        $this->setControllerToURIs()
                ->do($callback); 

        return $this;
    }

    /**
     * @inheritDoc
     */

    /**
     * @method URICollection do(\Closure $callback) - applys the callback to all the chained URIs
     * @param \Closure $callback
     * @return \AbdelrhmanSaeed\Route\URI\URICollection
     */
    private function do(\Closure $callback): URICollection
    {
        $URI = $this->URI;

        do {
            $callback($URI);
            $URI = $URI->getNext();
        }
        while( ! is_null($URI));

        return $this;
    }

    public function where(string $segment, string $regex): URICollection
    {
        $callback = function (AbstractURI $URI) use ($segment, $regex): void
                    {
                        $URI->where($segment, $regex);
                    };

        return $this->do($callback);
    }
    
    /**
     * @inheritDoc
     */
    public function whereIn(string $segment, array $in): URICollection
    {
        $callback = function (URI $URI) use ($segment, $in): void
                    {
                        $URI->whereIn($segment, $in);
                    };

        return $this->do($callback);
    }
    
    /**
     * @inheritDoc
     */
    public function whereOptional(array|string $regex): URICollection
    {
        $callback = function (URI $URI) use ($regex): void
                    {
                        $URI->whereOptional($regex);
                    };

        return $this->do($callback);
    }
}