<?php

namespace AbdelrhmanSaeed\Route\URI;

use AbdelrhmanSaeed\Route\{
	Middleware, URI\Constraints\URIConstraints, URI\URIActions\URIAction
};
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractURI
{
    protected array $middlewares = [];
    protected ?string $name = null;
    protected ?AbstractURI $next = null;
    protected URIConstraints $URIConstraints;

    public function __construct(
            protected string $route, protected array $methods, protected ?URIAction $URIAction
        )
    {
        $this->route = "#^$this->route$#";
    }

	/**
	 * @return 
	 */
	public function getName(): ?string {
		return $this->name;
	}
	
	/**
	 * @param  $name 
	 * @return self
	 */
	public function setName(?string $name): self {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return 
	 */
	public function getNext(): ?self {
		return $this->next;
	}
	
	/**
	 * @param  $next 
	 * @return self
	 */
	public function setNext(?self $next): AbstractURI {
		return $this->next = $next;
	}

	/**
	 * @return 
	 */
	public function getMiddlewares(): array {
		return $this->middlewares;
	}
	
	/**
	 * @param  $middleware 
	 * @return self
	 */
	public function setMiddlewares(string ...$middlewares): self {
		$this->middlewares = $middlewares;
		return $this;
	}

	/**
	 * @return URIConstraints
	 */
	public function getUriconstraints(): URIConstraints {
		return $this->URIConstraints;
	}
	
	/**
	 * @param URIConstraints $URIConstraints 
	 * @return self
	 */
	public function setUriconstraints(URIConstraints $URIConstraints): self {
		$this->URIConstraints = $URIConstraints;
		return $this;
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
	
	/**
	 * @return URIAction
	 */
	public function getURIAction(): URIAction {
		return $this->URIAction;
	}
	
	/**
	 * @param URIAction $URIAction 
	 * @return self
	 */
	public function setURIAction(URIAction $URIAction): self {
		$this->URIAction = $URIAction;
		return $this;
	}

    abstract public function handle(Request $request): void;
}