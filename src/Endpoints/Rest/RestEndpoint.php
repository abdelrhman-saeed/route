<?php


namespace AbdelrhmanSaeed\Route\Endpoints\Rest;

use AbdelrhmanSaeed\Route\Endpoints\Endpoint;
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsTrait;
use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use AbdelrhmanSaeed\Route\Resolvers\Resolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RestEndpoint extends Endpoint implements Rest
{
    use ConstraintsTrait, RestTrait;

    

    public function __construct(
            private string $route, private array $methods,
            protected ?Resolver $resolver = null
        )
    {
        $this->route = "#^$this->route$#";
    }


    /**
     * Summary of handle
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Request|null $request
     */
    public function handle(Request $request): null|Response
    {
        $this->setRoute($this->formatRouteToRegexPattern($this->getRoute()));

        $pathInfo = trim($request->getPathInfo(), '/');

        if ( ! preg_match($this->getRoute(), $pathInfo, $matches)
                || ! in_array(strtolower($request->getMethod()), $this->getMethods()) )
        {
            /**
             * making a chain of URI objects applying the chain of responsibility design pattern
             * each URI object in the chain will try to handle the request
             * till one object handles it
             */
            return $this->next?->handle($request);
        }

        unset($matches[0]);

        $response = $this->instantiateMiddlewares()?->handle($request);

        if (! is_null($response)) {
            return $response;
        }

        if (is_a($response = $this->getResolver()->execute(...$matches),
            Response::class)) {

            return $response;
        }

        return new Response();
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