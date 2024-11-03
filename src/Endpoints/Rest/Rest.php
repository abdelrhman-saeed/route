<?php

namespace AbdelrhmanSaeed\Route\Endpoints\Rest;

use AbdelrhmanSaeed\Route\Endpoints\Endpoint;
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsInterface;
use Symfony\Component\HttpFoundation\Request;


abstract class Rest extends Endpoint implements ConstraintsInterface
{

    /**
     * Summary of next
     * @var 
     */
    protected ?Rest $next = null;

    public function setNext(Rest $endpoint): Rest
    {
        return $this->next = $endpoint;
    }

	/**
	 * Summary of next
	 * @return 
	 */
	public function getNext(): ?Rest {
		return $this->next;
	}

    abstract public function handle(Request $request): void;
}