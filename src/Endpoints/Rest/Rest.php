<?php

namespace AbdelrhmanSaeed\Route\Endpoints\Rest;

use AbdelrhmanSaeed\Route\Endpoints\Endpoint;
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsInterface;
use Symfony\Component\HttpFoundation\{Request, Response};


interface Rest extends ConstraintsInterface
{

    /**
     * Summary of setNext
     * @param \AbdelrhmanSaeed\Route\Endpoints\Rest\Rest $endpoint
     * @return \AbdelrhmanSaeed\Route\Endpoints\Rest\Rest
     */
    public function setNext(self $endpoint): Rest;

	/**
	 * Summary of next
	 * @return null|Rest
	 */
	public function getNext(): ?Rest;

    /**
     * Summary of handle
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return null|Response
     */
    public function handle(Request $request): null|Response;
}