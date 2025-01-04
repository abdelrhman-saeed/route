<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use Attribute;
use GraphQL\Type\Definition\{InterfaceType, Type};
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObjectBuilder;


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