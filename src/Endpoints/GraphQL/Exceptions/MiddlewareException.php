<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Exceptions;

use GraphQL\Error\ClientAware;
use Symfony\Component\HttpFoundation\Response;


class MiddlewareException extends \Exception implements ClientAware
{
    public function isClientSafe(): bool {
        return true;
    }
}