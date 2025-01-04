<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use GraphQl\Type\Definition\Type;


class NotNull extends GraphObject
{
    public function __construct(GraphObject $baseGraphObject) {
        $this->type = \GraphQL\Type\Definition\Type::nonNull($baseGraphObject->build());
    }

    public function build(): Type
    {
        return $this->type;
    }
}