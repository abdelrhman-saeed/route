<?php

namespace AbdelrhmanSaeed\Route\URI;

use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use AbdelrhmanSaeed\Route\URI\Constraints\UriConstraints;
use AbdelrhmanSaeed\Route\Middleware;
use Symfony\Component\HttpFoundation\Request;

class URI
{
    private ?string $name = null;
    private ?URI $next = null;
    private ?Middleware $middleware = null;
    private URIConstraints $uriConstraints;

    public function __construct(private string $route, private array $methods, private URIAction $uriAction)
    {
        $this->route = "#^$this->route$#";
    }

    private function getUriAction(): URIAction
    {
        return $this->uriAction;
    }

    private function getMethods(): array
    {
        return $this->methods;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function setNext(URI $uri): self
    {
        $this->next = $uri;

        return $this;
    }

    public function setUriConstraints(URIConstraints $uriConstraints): self
    {
        $this->uriConstraints = $uriConstraints;

        return $this;
    }

    public function getUriConstraints(): URIConstraints
    {
        return $this->uriConstraints;
    }
    public function setMiddleware(Middleware $middelware): self
    {
        $this->middleware = $middelware;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }


    public function handle(Request $request): void
    {
        $pathInfo = trim($request->getPathInfo(), '/');

        if ( ! preg_match($this->getRoute(), $pathInfo, $matches)
                || ! in_array(strtolower($request->getMethod()), $this->getMethods()) )
        {
            $this->next?->handle($request);
            return;
        }

        unset($matches[0]);

        $this->middleware?->handle($request);

        $this->getUriAction()
                ->execute(...$matches);

        throw new RequestIsHandledException();
    }
}