<?php

use PHPUnit\Framework\TestCase;
use AbdelrhmanSaeed\Route\URI\{
        URI,
        Constraints\IURIConstraints,
        Constraints\URIConstraints
};

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
                ->method('getRoute')
                ->willReturn($route);

        $this->uriMock
                ->expects($this->once())
                ->method('setRoute')
                ->with($regexedRoute);

        $reflectedReplaceRouteSegmentMethod =
                (new \ReflectionClass(URIConstraints::class))
                        ->getMethod('replaceRouteSegmentsWithRegex');

        $reflectedReplaceRouteSegmentMethod
                ->invokeArgs(
                        $this->uriConstraintsMock,
                        [IURIConstraints::ALPHANUM, IURIConstraints::ALPHANUM
                ]);
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