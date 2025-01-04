<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\{
    Reflected, ReflectedClass, ReflectedEnum
};


class Union extends GraphObject
{
    /**
     * @param Type[] $types
     */
    public function __construct(protected array $types, protected Reflected|ReflectedEnum|ReflectedClass $reflected) {

    }

    public function build(): Type
    {
        if (! is_null($this->type)) {
            return $this->type;
        }

        return $this->type =
            new UnionType([
                'name'          => $this->reflected->getName(),
                'description'   => $this->reflected->getDescriptionFromDocBlock(),
                'types'         => $this->types,
                'resolveType'   =>
                    function ($value): Type
                    {
                        return GraphObjectBuilder::getGraphObjectByName(
                                    get_class($value)
                                )->build();
                    },
            ]);
    }
}