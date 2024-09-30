<?php

namespace AbdelrhmanSaeed\Route\URI;

use AbdelrhmanSaeed\Route\Exceptions\MethodNotSupportedForThisRoute;
use AbdelrhmanSaeed\Route\Middleware;
use Symfony\Component\HttpFoundation\Request;

class URI
{
    private string $url;
    private ?string $name = null;
    private ?URI $next = null;
    private ?Middleware $middleware = null;
    private URIConstraints $uriConstraints;
    public static array $supportedHttpMethods = ['get', 'post', 'put', 'patch', 'delete'];

    public function __construct(private string $route, private string $method, private URIAction $uriAction)
    {
        $this->url = "#^$this->route$#";
    }

    private function getUriAction(): URIAction
    {
        return $this->uriAction;
    }

    private function getMethod(): string
    {
        return $this->method;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRoute(): string
    {
        return $this->route;
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
        $this->getUriConstraints()->formatUrlToRegex();

        $pathInfo = trim($request->getPathInfo(), '/');

        if ( ! preg_match($this->getUrl(), $pathInfo, $matches))
        {
            $this->next?->handle($request);
            return;
        }

        unset($matches[0]);

        if (! $request->isMethod($this->getMethod()))
        {
            throw new MethodNotSupportedForThisRoute(
                "the {$request->getMethod()} method is not supported for this route, supported method is $this->method!"
            );
        }

        $this->middleware?->handle($request);

        $this->getUriAction()
                ->execute(...$matches);
    }
}
