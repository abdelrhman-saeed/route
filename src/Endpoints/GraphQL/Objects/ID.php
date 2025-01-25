<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use GraphQL\Type\Definition\Type;


class ID extends GraphObject
{
    public function build(): Type {
        return Type::id();
    }
}