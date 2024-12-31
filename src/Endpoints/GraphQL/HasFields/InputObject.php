<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedParameter;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Attribute;


#[Attribute(Attribute::TARGET_CLASS)]
class InputObject extends HasFields
{

    public function build(): Type
    {
        if (! is_null($this->type)) {
            return $this->type;
        }

        $this->setupFieldsFromProperties();

        return $this->type = new InputObjectType($this->config);
    }
}