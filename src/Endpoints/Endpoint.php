<?php

namespace AbdelrhmanSaeed\Route\Endpoints;

use AbdelrhmanSaeed\Route\Middlewares\Middleware;
use AbdelrhmanSaeed\Route\Resolvers\Resolver;


abstract class Endpoint
{
    /**
     * @var Middleware[]
     */
    protected array $middlewares = [];

    /**
     * @param \AbdelrhmanSaeed\Route\Resolvers\Resolver|null $resolver
     */
    public function __construct(protected ?Resolver $resolver = null) {

    }

    /**
     * Summary of setResolver
     * @param \AbdelrhmanSaeed\Route\Resolvers\Resolver $resolver
     * @return \AbdelrhmanSaeed\Route\Endpoints\Endpoint
     */
    public function setResolver(Resolver $resolver): self
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @return \AbdelrhmanSaeed\Route\Resolvers\Resolver
     */
    public function getResolver(): Resolver
    {
        return $this->resolver;
    }


    /**
     * @param string[] $middlewares
     * @return \AbdelrhmanSaeed\Route\Endpoints\Endpoint
     */
    public function setMiddlewares(string ...$middlewares): static
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * chaining the Middleware[] $middleware objects and letting them call each other one by one
     * giving them the chance to decide whether to stop handling the request or not
     * 
     * SIMPLY appling the chain of responsibility design pattern
     * 
     * @return Middleware|null
     */
    protected function instantiateMiddlewares(): ?Middleware
    {
        if (empty($this->middlewares)) {
            return null;
        }

        /**
         * @var Middleware $head
         * @var Middleware $current
         */
        $head = $current = new $this->middlewares[0];

        for($i = 1; $i < count($this->middlewares); $i++)
        {
            $current =
                $current->setNext(new $this->middlewares[$i])
                        ->getNext();
        }

        return $head;
    }
}