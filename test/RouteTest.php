<?php

use AbdelrhmanSaeed\Route\Exceptions\NotSupportedHttpMethodException;
use PHPUnit\Framework\TestCase;
use AbdelrhmanSaeed\Route\Route;


class RouteTest extends TestCase
{
    public function test__callstaticWithNotSupportedHttpMethod(): void
    {
        $this->expectException(NotSupportedHttpMethodException::class);
        Route::notSupportedHttpMethod();
    }
}