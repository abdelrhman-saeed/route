<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use Attribute;
use GraphQL\Type\Definition\{ObjectType, Type};
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObjectBuilder;


#[Attribute(Attribute::TARGET_CLASS)]
class Output extends HasFields
{
    public function build(): Type
    {
        if ($this->type !== null) {
            return $this->type;
        }

        $this->setupFields();

        $interfaces = [
            get_parent_class($this->reflected->getName()),
                ... class_implements($this->reflected->getName())
        ];

        foreach ($interfaces as $interface)
        {
            if (! is_null($interface = GraphObjectBuilder::getGraphObjectByName($interface))) {
                $this->config['interfaces'][] = $interface->build();
            }
        }

        return $this->type = new ObjectType($this->config);
    }
}