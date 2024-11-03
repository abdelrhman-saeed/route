<?php


namespace AbdelrhmanSaeed\Route\Endpoints\Rest;

use AbdelrhmanSaeed\Route\Endpoints\Endpoint;
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsInterface;
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsTrait;
use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use AbdelrhmanSaeed\Route\Middlewares\Middleware;
use AbdelrhmanSaeed\Route\Resolvers\Resolver;
use Symfony\Component\HttpFoundation\Request;


class RestEndpoint extends Rest
{
    use ConstraintsTrait;

    

    public function __construct(
            private string $route, private array $methods,
            protected ?Resolver $resolver = null
        )
    {
        $this->route = "#^$this->route$#";
    }


    /**
     * handles the client request
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException
     * @return void
     */
    public function handle(Request $request): void
    {
       $this->setRoute( $this->formatRouteToRegexPattern($this->getRoute()) );

        $pathInfo = trim($request->getPathInfo(), '/');

        if ( ! preg_match($this->getRoute(), $pathInfo, $matches)
                || ! in_array(strtolower($request->getMethod()), $this->getMethods()) )
        {
            $this->next?->handle($request);

            /**
             * making a chain of URI objects applying the chain of responsibility design pattern
             * each URI object in the chain will try to handle the request
             * till one object handles it
             */

            return;
        }

        unset($matches[0]);

        $this->instantiateMiddlewares()?->handle($request);

        $this->getResolver()
                ->execute(...$matches);

        throw new RequestIsHandledException();
    }

	/**
	 * @return string
	 */
	public function getRoute(): string {
		return $this->route;
	}
	
	/**
	 * @param string $route 
	 * @return self
	 */
	public function setRoute(string $route): self {
		$this->route = $route;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getMethods(): array {
		return $this->methods;
	}
	
	/**
	 * @param array $methods 
	 * @return self
	 */
	public function setMethods(array $methods): self {
		$this->methods = $methods;
		return $this;
	}
}