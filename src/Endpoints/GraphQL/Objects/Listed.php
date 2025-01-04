<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;


class Listed extends GraphObject
{
    public function __construct(private GraphObject $baseGraphObject) {

    }

    public function build(): Type {
        return new ListOfType($this->baseGraphObject->build());
    }
}