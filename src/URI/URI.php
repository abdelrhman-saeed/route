<?php

namespace AbdelrhmanSaeed\Route\URI;

use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use AbdelrhmanSaeed\Route\Middleware;
use AbdelrhmanSaeed\Route\URI\Constraints\URIConstraintsTrait;
use Symfony\Component\HttpFoundation\Request;

class URI extends AbstractURI
{
    use URIConstraintsTrait;

    private function prepareMiddlewares(): ?Middleware
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
            $current->setNext(new $this->middlewares[$i]);
            $current = $current->getNext();
        }

        return $head;
    }

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

        $this->prepareMiddlewares()?->handle($request);

        $this->getUriAction()
                ->execute(...$matches);

        throw new RequestIsHandledException();
    }
}