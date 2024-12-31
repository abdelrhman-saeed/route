<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;


class ListedObject extends BaseGraphObject
{
    public function __construct(private BaseGraphObject $baseGraphObject) {

    }

    public function build(): Type {
        return new ListOfType($this->baseGraphObject->build());
    }
}