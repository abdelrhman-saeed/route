<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL;

use GraphQL\Type\Definition\{ScalarType, StringType, IntType, BooleanType, FloatType};
use GraphQL\Type\Definition\Type;

class ScalarObject extends BaseGraphObject
{
    public static function scalars(string $type): ?ScalarType
    {
        return match($type) {
            'string'    => Type::string(),
            'int'       => Type::int(),
            'float'     => Type::float(),
            'boolean'   => Type::boolean(),
            default     => null
        };
    }
    public function __construct(string $type) {
        $this->type = self::scalars($type);
    }

    public function build(): Type
    {
        return $this->type;
    }
}