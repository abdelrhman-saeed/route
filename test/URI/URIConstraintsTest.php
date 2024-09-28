<?php

use PHPUnit\Framework\TestCase;
use AbdelrhmanSaeed\Route\URI\{URI, URIConstraints};

class URIConstraintsTest extends TestCase
{
    private  $uriMock;
    private $uriConstraintsMock;
    private URIConstraints $uriConstraints;
    protected function setUp(): void {

        $this->uriMock          = $this->createMock(URI::class);
        $this->uriConstraints   = new URIConstraints($this->uriMock);

        $this->uriConstraintsMock = $this->getMockBuilder(URIConstraints::class)
                                            ->disableOriginalConstructor()
                                            ->onlyMethods(['where'])
                                            ->getMock();
    }

    public function testWhere(): void
    { 
        $this->uriMock
                ->expects(self::once())
                ->method('getUrl')
                ->willReturn('/path/{routeseg}/{routeseg2}/path/');
        
        $this->uriMock
                ->expects(self::once())
                ->method('setUrl')
                ->with('/path/\w+/{routeseg2}/path/');

        $this->uriConstraints
                ->where('routeseg', URIConstraints::ALPHA);

    }

    public function testWhereIn(): void
    {
        $this->uriMock
                ->expects(self::once())
                ->method('getUrl')
                ->willReturn('/path/{routeseg}/');

        $this->uriMock
                ->expects(self::once())
                ->method('setUrl')
                ->with('/path/(one|two)/');

        $this->assertSame(
                $this->uriConstraints->whereIn('routeseg', ['one', 'two']),
                $this->uriConstraints
            );
    }
 
    public function testWhereOptional(): void
    {
        $this->uriConstraintsMock->expects($this->once())
                                    ->method('where')
                                    ->with(URIConstraints::OPTIONAL_PARAMETER_REGEX, '(' . URIConstraints::OPTIONAL_PARAMETER_REGEX . ')*');
 
        $this->uriConstraintsMock->whereOptional(URIConstraints::OPTIONAL_PARAMETER_REGEX);
    }

    public function testWhereOptionalWithSpecifiedValues(): void
    {
        $this->uriConstraintsMock->expects($this->once())
                                    ->method('where')
                                    ->with(URIConstraints::OPTIONAL_PARAMETER_REGEX, '(one|two)*');
 
        $this->uriConstraintsMock->whereOptional(['one', 'two']);
    }

    public function testFormatUrlToRegex(): void
    {
        $this->uriConstraintsMock->expects(self::exactly(2))
                                    ->method('where');

        $this->uriConstraintsMock->formatUrlToRegex();
    }
}
