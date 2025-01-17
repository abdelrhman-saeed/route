<?php

namespace AbdelrhmanSaeed\Route\Middlewares;

use Symfony\Component\HttpFoundation\{Request, Response};


abstract class Middleware
{
    /**
     * a pointer to the next middleware
     * 
     * a pointer to the next middleware
     * in case if the middleware will pass the handled request
     * to the next one
     * @var null|Middleware
     */
    private ?Middleware $next = null;

    /**
     * @param \AbdelrhmanSaeed\Route\Middlewares\Middleware $next
     * @return \AbdelrhmanSaeed\Route\Middlewares\Middleware
     */
    public function setNext(Middleware $next): self {
        $this->next = $next;
        return $this;
    }

    /**
     * @return Middleware|null
     */
    public function getNext(): ?Middleware {
        return $this->next;
    }

    /**
     * Summary of handle
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function handle(Request $request): ?Response {
        return $this->next?->handle($request);
    }
}