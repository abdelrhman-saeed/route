<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use GraphQL\Type\Definition\{Type, ScalarType};
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\ID;


class Scalar extends GraphObject
{
    public static function scalars(string $type): ?ScalarType
    {
        return match ($type) {

            ID::class   => Type::id(),

            'string'    => Type::string(),
            'int'       => Type::int(),
            'float'     => Type::float(),
            'bool'      => Type::boolean(),

            default => null
        };
    }
    public function __construct(string $type)
    {
        $this->type = self::scalars($type);
    }

    public function build(): Type
    {
        return $this->type;
    }
}
