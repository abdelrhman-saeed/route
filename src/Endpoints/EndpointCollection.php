<?php

namespace AbdelrhmanSaeed\Route\Endpoints;

use AbdelrhmanSaeed\Route\Resolvers\Resolver;


abstract class EndpointCollection extends Endpoint
{
    /**
     * @param mixed $endpoint
     * @param mixed $resolver
     */
    public function __construct(
        protected ?Endpoint $endpoint = null,
        protected ?Resolver $resolver = null) {
    }

    /**
     * @param string $controller
     * @return \AbdelrhmanSaeed\Route\Endpoints\EndpointCollection
     */
    public function setController(string $controller): self {
        return $this->setResolver(new Resolver($controller));
    }

    /**
     * sets the Resolver object to the endpoints objects in the EndpointCollection object
     * @return static
     */
    abstract public function setResolverToEndpoints(): static;
    
    /**
     * @param \Closure $callback
     * @return static
     */
    abstract public function group(\Closure $callback): static;
}