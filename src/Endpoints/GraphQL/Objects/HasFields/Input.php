<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use GraphQL\Type\Definition\{InputObjectType, Type};
use Attribute;


#[Attribute(Attribute::TARGET_CLASS)]
class Input extends HasFields
{

    public function build(): Type
    {
        if (! is_null($this->type)) {
            return $this->type;
        }

        $this->setupFields();

        return $this->type = new InputObjectType($this->config);
    }
}