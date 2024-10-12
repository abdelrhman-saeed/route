<?php

use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use PHPUnit\Framework\TestCase;
use AbdelrhmanSaeed\Route\URI\{AbstractURI, URI, URICollection};
use Symfony\Component\HttpFoundation\Request;

class URICollectionTest extends TestCase
{
    
    private $URIMock;
    private $URIConstraintsMock;
    private $URICollection;
    private Request $requestMock;

    public function setUp(): void
    {
        $this->URIMock              = $this->createMock(URI::class);
        $this->requestMock          = $this->createMock(Request::class);

        $this->URICollection        = new URICollection($this->URIMock);
    
    }
    public function testHandle(): void
    {
        $this->URICollection->setNext($this->URIMock);

        $this->URIMock
                ->expects($this->exactly(2))
                ->method('handle')
                ->with($this->requestMock);

        $this->URICollection->handle($this->requestMock);

    }

    public function testHandleIfURIThrowsException(): void
    {

        $this->URICollection
                ->setNext($URIMock2 = $this->createMock(URI::class));

        $this->expectException(RequestIsHandledException::class);

        $this->URIMock->expects($this->once())
                        ->method('handle')
                        ->with($this->requestMock)
                        ->will($this->throwException(new RequestIsHandledException()));


        $URIMock2->expects($this->never())
                    ->method('handle')
                    ->with($this->requestMock);

        $this->URICollection->handle($this->requestMock);
    }

    public function testDo(): void
    {

        $segment = 'someRouteSegment';
        $regex   = 'someRouteRegex';

        $callback = function (URI $URI) use ($segment, $regex): void
                    {
                        $URI->where($segment, $regex);
                    };

        $this->URIMock
                ->expects($this->once())
                ->method('where')
                ->with($segment, $regex);


        $this->URIMock->expects($this->once())
                        ->method('getNext');

            
        $reflectedDo =
            (new \ReflectionClass(URICollection::class))
                    ->getMethod('do');

        $reflectedDo->invokeArgs( $this->URICollection, [$callback]);

    }
}