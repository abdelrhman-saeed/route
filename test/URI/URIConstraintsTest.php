<?php

use PHPUnit\Framework\TestCase;
use AbdelrhmanSaeed\Route\URI\{URI, URIConstraints};

class URIConstraintsTest extends TestCase
{
    private  $uriMock;
    private $uriConstraintsMock;
    protected function setUp(): void {

        $this->uriMock              = $this->createMock(URI::class);
        $this->uriConstraintsMock   = $this->getMockBuilder(URIConstraints::class)
                                            ->setConstructorArgs([$this->uriMock])
                                            ->onlyMethods(['where'])
                                            ->getMock();
    }

    
    public function testReplaceRouteSegmentsWithRegex(): void
    {
        $route          = 'users/{user}/posts/{post}'; 
        $regexedRoute   = 'users/\w+/posts/\w+';

        $this->uriMock
                ->expects($this->once())    
                ->method('getUrl')
                ->willReturn($route);

        $this->uriMock
                ->expects($this->once())
                ->method('setUrl')
                ->with($regexedRoute);

        $reflectedMethod = (new \ReflectionClass(URIConstraints::class))
                                    ->getMethod('replaceRouteSegmentsWithRegex');

        $reflectedMethod->invokeArgs(
                $this->uriConstraintsMock,
                [URIConstraints::ALPHANUM, URIConstraints::ALPHANUM]);
    }
    public function testWhereIn(): void
    {
        $this->uriConstraintsMock
                ->expects($this->once())    
                ->method('where')
                ->with('posts', '(dummy|dummy)');

        $this->uriConstraintsMock->whereIn('posts', ['dummy', 'dummy']);
    }
}