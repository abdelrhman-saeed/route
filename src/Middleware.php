<?php

namespace AbdelrhmanSaeed\Route;

use Symfony\Component\HttpFoundation\Request;


abstract class Middleware
{
    private ?Middleware $next = null;

    public function setNext(Middleware $next): self
    {
        $this->next = $next;
        return $this;
    }

    public function getNext(): Middleware
    {
        return $this->next;
    }

    public function handle(Request $request): void
    {
        $this->next?->handle($request);
    }
}