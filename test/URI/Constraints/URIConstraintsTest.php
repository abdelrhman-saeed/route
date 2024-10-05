<?php

use PHPUnit\Framework\TestCase;
use AbdelrhmanSaeed\Route\URI\{
        URI,
        Constraints\IURIConstraints,
        Constraints\URIConstraints
};

class URIConstraintsTest extends TestCase
{
    private  $URIMock;
    private $URIConstraints;
    protected function setUp(): void {

        $this->URIMock          = $this->createMock(URI::class);
        $this->URIConstraints   = new URIConstraints($this->URIMock);
    }

    
    public function testFormatRouteToRegexPattern(): void
    {

        $route          = 'users/{user}/posts/{post}'; 
        $regexedRoute   = 'users/([a-z]+)/posts/(\w+)';

        $this->URIMock
                ->expects($this->once())
                ->method('getRoute')
                ->willReturn($route);

        $this->URIMock
                ->expects($this->once())
                ->method('setRoute')
                ->with($regexedRoute);

        $this->URIConstraints
                ->where('user', \AbdelrhmanSaeed\Route\URI\Constraints\URIConstraintsInterface::ALPHA)
                ->formatRouteToRegexPattern();
    }
}