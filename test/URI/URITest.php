<?php

use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use AbdelrhmanSaeed\Route\Middleware;
use AbdelrhmanSaeed\Route\URI\{URI, URIActions\URIAction, Constraints\URIConstraintsTrait};
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;


class URITest extends TestCase
{

    private $uriActionMock;
    private $middlewareMock;
    private $uriMock;
    private $requestMock;

    private string $url, $method, $pathInfo, $regexedRoute;

    public function setUp(): void
    {
        $this->uriActionMock    = $this->createMock(URIAction::class);
        $this->requestMock      = $this->createMock(Request::class);

        $this->pathInfo         = 'users/22/posts/3/information';
        $this->url              = 'users/{user}/posts/{post}/{info?}';
        $this->regexedRoute     = '#^users/(\w+)/posts/(\w+)/?(\w+)*$#';
        $this->method           = 'get';

        $this->uriMock = $this->getMockBuilder(URI::class)
                                ->enableProxyingToOriginalMethods()
                                ->setConstructorArgs([
                                    $this->url,
                                    [$this->method],
                                    $this->uriActionMock
                                ])
                                ->onlyMethods(['getRoute', 'setRoute'])
                                ->getMock();
    }
    public function testHandle(): void
    {
        
        $this->uriMock
                ->expects($this->exactly(2))
                ->method('getRoute')
                ->willReturn($this->url);
        
        $this->uriMock
                ->expects($this->once())
                ->method('setRoute')
                ->with($this->regexedRoute);

        $this->requestMock
                ->expects($this->once())
                ->method('getPathInfo')
                ->willReturn($this->pathInfo);

        $this->uriMock
                // ->expects($this->once())        
                ->method('getRoute')
                ->willReturn($this->regexedRoute);

        $this->requestMock
                ->expects($this->once())
                ->method('getMethod')
                ->willReturn($this->method);


        $this->uriActionMock
                ->expects($this->once())
                ->method('execute')
                ->with(...['22', '3', 'information']);

        $this->expectException(RequestIsHandledException::class);

        $this->uriMock
                ->handle($this->requestMock);

    }

    public function testHandleMethodWhenUriDoesntMatchUrl(): void
    {
       $this->uriMock
                ->expects($this->exactly(2))
                ->method('getRoute')
                ->willReturnOnConsecutiveCalls(
                        $this->url, '#just-wrong-pattern-to-make-the-regex-fail#'
                );
        
        $this->uriMock
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

        $otherUriMock = $this->createMock(URI::class);
 
        $this->uriMock
                ->setNext($otherUriMock);
 
        $otherUriMock->expects($this->once())
                        ->method('handle')
                        ->with($this->requestMock);

        $this->uriMock->handle($this->requestMock);

    }
}