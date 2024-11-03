<?php

use AbdelrhmanSaeed\Route\API\Route;
use AbdelrhmanSaeed\Route\Exceptions\NotSupportedHttpMethodException;
use PHPUnit\Framework\TestCase;


class RouteTest extends TestCase
{
    public function test__callstaticWithNotSupportedHttpMethod(): void
    {
        $this->expectException(NotSupportedHttpMethodException::class);
        Route::notSupportedHttpMethod();
    }

    public function testPrepareResourceRoute(): void
    {

        $prepareResourceRouteMethod = (new \ReflectionClass(Route::class))
                                            ->getMethod('prepareResourceRoute');

        $output = $prepareResourceRouteMethod
                        ->invokeArgs(new Route, ['users.posts', true]);

        $expected = [
            'general' => 'users/{users}/posts',
            'identified' => 'users/posts/{posts}'
        ];

        $this->assertSame($expected, $output);

        $output = $prepareResourceRouteMethod
                        ->invokeArgs(new Route, ['users.posts', false]);

        $expected = [
            'general' => 'users/{users}/posts',
            'identified' => 'users/{users}/posts/{posts}'
        ];

        $this->assertSame($expected, $output);
    }
}