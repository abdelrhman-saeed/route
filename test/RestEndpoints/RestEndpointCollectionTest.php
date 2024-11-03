<?php

use AbdelrhmanSaeed\Route\Endpoints\Rest\RestEndpoint;
use AbdelrhmanSaeed\Route\Endpoints\Rest\RestEndpointCollection;
use AbdelrhmanSaeed\Route\E;
use AbdelrhmanSaeed\Route\Endpoints\Rest\RestEndpointException;
use AbdelrhmanSaeed\Route\Exceptions\RequestIsHandledException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class restEndpointCollectionTest extends TestCase
{
    
    private $restEndpointMock;
    private $restEndpointCollection;
    private Request $requestMock;

    public function setUp(): void
    {
        $this->restEndpointMock              = $this->createMock(RestEndpoint::class);
        $this->requestMock                   = $this->createMock(Request::class);

        $this->restEndpointCollection        = new RestEndpointCollection($this->restEndpointMock);
    
    }
    public function testHandle(): void
    {
        $this->restEndpointCollection->setNext($this->restEndpointMock);

        $this->restEndpointMock
                ->expects($this->exactly(2))
                ->method('handle')
                ->with($this->requestMock);

        $this->restEndpointCollection->handle($this->requestMock);

    }

    public function testHandleIfURIThrowsException(): void
    {

        $this->restEndpointCollection
                ->setNext($restEndpointMock2 = $this->createMock(RestEndpoint::class));

        $this->expectException(RequestIsHandledException::class);

        $this->restEndpointMock->expects($this->once())
                        ->method('handle')
                        ->with($this->requestMock)
                        ->will($this->throwException(new RequestIsHandledException()));


        $restEndpointMock2->expects($this->never())
                    ->method('handle')
                    ->with($this->requestMock);

        $this->restEndpointCollection->handle($this->requestMock);
    }

    public function testDo(): void
    {

        $segment = 'someRouteSegment';
        $regex   = 'someRouteRegex';

        $callback = function (RestEndpoint $restEndpoint) use ($segment, $regex): void {
                        $restEndpoint->where($segment, $regex);
                    };

        $this->restEndpointMock
                ->expects($this->once())
                ->method('where')
                ->with($segment, $regex);


        $this->restEndpointMock->expects($this->once())
                        ->method('getNext');

            
        $reflectedDo =
            (new \ReflectionClass(restEndpointCollection::class))
                    ->getMethod('do');

        $reflectedDo->invokeArgs( $this->restEndpointCollection, [$callback]);

    }
}