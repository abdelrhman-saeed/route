<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass;
use AbdelrhmanSaeed\Route\API\GraphQL;
use GraphQL\Type\Definition\{InterfaceType, ObjectType, Type};
use Attribute;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\GraphObjectBuilder;


#[Attribute(Attribute::TARGET_CLASS)]
class InterfaceObject extends HasFields
{
    public function build(): Type
    {
        if (! is_null($this->type)) {
            return $this->type;
        }

        $this->setupFieldsFromProperties();

        $this->config['resolveType'] =
            function ($value): Type {
                return GraphObjectBuilder::getGraphObjectByName(get_class($value))->build();
            };

        return $this->type = new InterfaceType($this->config);
    }
}