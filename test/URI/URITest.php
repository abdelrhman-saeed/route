<?php

use AbdelrhmanSaeed\Route\Exceptions\MethodNotSupportedForThisRoute;
use AbdelrhmanSaeed\Route\Middleware;
use AbdelrhmanSaeed\Route\URI\{
        URI,
        URIAction,
        Constraints\URIConstraints,
};
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;


class URITest extends TestCase
{

    private $uriActionMock;
    private $uriConstraintsMock;
    private $middlewareMock;
    private $uriMock;
    private $requestMock;

    private string $url, $method, $pathInfo, $regexedRoute;

    public function setUp(): void
    {
        $this->uriActionMock        = $this->createMock(URIAction::class);
        $this->uriConstraintsMock   = $this->createMock(URIConstraints::class);
        $this->middlewareMock       = $this->createMock(Middleware::class);

        $this->requestMock          = $this->createMock(Request::class);

        $this->pathInfo         = 'users/22/posts/3/information';
        $this->url              = 'users/{user}/posts/{post}/{info?}';
        $this->regexedRoute     = '#^users/(\w+)/posts/(\w+)/?(\w+)*$#';
        $this->method           = 'get';

        $this->uriMock = $this->getMockBuilder(URI::class)
                                ->setConstructorArgs([
                                    $this->url,
                                    $this->method,
                                    $this->uriActionMock
                                ])
                                ->onlyMethods(['getRoute'])
                                ->getMock();

        $this->uriMock
                ->setUriConstraints($this->uriConstraintsMock)
                ->setMiddleware($this->middlewareMock);


    }
    public function testHandle(): void
    {
        $this->uriConstraintsMock
                ->expects($this->once())
                ->method('formatUrlToRegex');

        $this->requestMock
                ->expects($this->once())
                ->method('getPathInfo')
                ->willReturn($this->pathInfo);

        $this->uriMock
                ->expects($this->once())        
                ->method('getRoute')
                ->willReturn($this->regexedRoute);

        $this->requestMock
                ->expects($this->once())
                ->method('isMethod')
                ->with($this->method)
                ->willReturn(true);

        $this->middlewareMock
                ->expects($this->once())    
                ->method('handle')
                ->with($this->requestMock);

        $this->uriActionMock
                ->expects($this->once())
                ->method('execute')
                ->with(...['22', '3', 'information']);

        $this->uriMock->handle($this->requestMock);
    }

    public function testHandleMethodWhenUriDoesntMatchUrl(): void
    {
        $this->uriConstraintsMock
                ->expects($this->once())
                ->method('formatUrlToRegex');

        $this->requestMock
                ->expects($this->once())
                ->method('getPathInfo')
                ->willReturn($this->pathInfo);

        $this->uriMock
                ->expects($this->once())
                ->method('getRoute')
                ->willReturn('just-wrong-pattern-to-make-the-regex-fail');


        $otherUriMock = $this->createMock(URI::class);

        $this->uriMock
                ->setNext($otherUriMock);

        $otherUriMock->expects($this->once())
                        ->method('handle')
                        ->with($this->requestMock);

        $this->uriMock->handle($this->requestMock);

    }

    public function testHandleMethodWhenHttpMethodIsWrong(): void
    {
        $this->uriConstraintsMock
                ->expects($this->once())
                ->method('formatUrlToRegex');

        $this->requestMock
                ->expects($this->once())
                ->method('getPathInfo')
                ->willReturn($this->pathInfo);

        $this->uriMock
                ->expects($this->once())        
                ->method('getRoute')
                ->willReturn($this->regexedRoute);

        $this->requestMock
                ->expects($this->once())
                ->method('isMethod')
                ->with($this->method)
                ->willReturn(false);

        $this->expectException(MethodNotSupportedForThisRoute::class);

        $this->uriMock
                ->handle($this->requestMock);
    }

}