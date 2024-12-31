<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL;

use GraphQl\Type\Definition\Type;


class NotNull extends BaseGraphObject
{
    public function __construct(BaseGraphObject $baseGraphObject) {
        $this->type = \GraphQL\Type\Definition\Type::nonNull($baseGraphObject->build());
    }

    public function build(): Type
    {
        return $this->type;
    }
}