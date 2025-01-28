<?php

namespace AbdelrhmanSaeed\Route\Endpoints\Rest;

use AbdelrhmanSaeed\Route\Endpoints\Endpoint;
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsInterface;
use Symfony\Component\HttpFoundation\{Request, Response};


trait RestTrait
{

    /**
     * Summary of next
     * @var null|Rest
     */
    public ?Rest $next = null;

    public function setNext(Rest $next): Rest {
        return $this->next = $next;
    }

	/**
	 * Summary of next
	 * @return Rest
	 */
	public function getNext(): ?Rest {
		return $this->next;
	}

    /**
     * Summary of handle
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    abstract public function handle(Request $request): null|Response;
}