<?php

use AbdelrhmanSaeed\Route\Endpoints\Rest\RestEndpoint;
use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use AbdelrhmanSaeed\Route\Middleware;
use AbdelrhmanSaeed\Route\Resolvers\Resolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;


class RestEndpointTest extends TestCase
{

    private $resolverMock;
    private $middlewareMock;
    private $restEndpointMock;
    private $requestMock;

    private string $url, $method, $pathInfo, $regexedRoute;

    public function setUp(): void
    {
        $this->resolverMock     = $this->createMock(Resolver::class);
        $this->requestMock      = $this->createMock(Request::class);

        $this->pathInfo         = 'users/22/posts/3/information';
        $this->url              = 'users/{user}/posts/{post}/{info?}';
        $this->regexedRoute     = '#^users/(\w+)/posts/(\w+)/?(\w+)*$#';
        $this->method           = 'get';

        $this->restEndpointMock = $this->getMockBuilder(RestEndpoint::class)
                                ->enableProxyingToOriginalMethods()
                                ->setConstructorArgs([
                                    $this->url,
                                    [$this->method],
                                    $this->resolverMock
                                ])
                                ->onlyMethods(['getRoute', 'setRoute'])
                                ->getMock();
    }
    public function testHandle(): void
    {
        
        $this->restEndpointMock
                ->expects($this->exactly(2))
                ->method('getRoute')
                ->willReturn($this->url);
        
        $this->restEndpointMock
                ->expects($this->once())
                ->method('setRoute')
                ->with($this->regexedRoute);

        $this->requestMock
                ->expects($this->once())
                ->method('getPathInfo')
                ->willReturn($this->pathInfo);

        $this->restEndpointMock
                // ->expects($this->once())        
                ->method('getRoute')
                ->willReturn($this->regexedRoute);

        $this->requestMock
                ->expects($this->once())
                ->method('getMethod')
                ->willReturn($this->method);


        $this->resolverMock
                ->expects($this->once())
                ->method('execute')
                ->with(...['22', '3', 'information']);

        $this->expectException(RequestIsHandledException::class);

        $this->restEndpointMock
                ->handle($this->requestMock);

    }

    public function testHandleMethodWhenUriDoesntMatchUrl(): void
    {
       $this->restEndpointMock
                ->expects($this->exactly(2))
                ->method('getRoute')
                ->willReturnOnConsecutiveCalls(
                        $this->url, '#just-wrong-pattern-to-make-the-regex-fail#'
                );
        
        $this->restEndpointMock
                ->expects($this->once())
                ->method('setRoute')
                ->with($this->regexedRoute);

        $this->requestMock
                ->expects($this->once())
                ->method('getPathInfo')
                ->willReturn($this->pathInfo);

        $this->requestMock
                ->expects($this->once())
                ->method('getMethod')
                ->willReturn('wrong-method');

        $endpointMock2 = $this->createMock(RestEndpoint::class);
 
        $this->restEndpointMock
                ->setNext($endpointMock2);
 
        $endpointMock2->expects($this->once())
                        ->method('handle')
                        ->with($this->requestMock);

        $this->restEndpointMock->handle($this->requestMock);

    }
}