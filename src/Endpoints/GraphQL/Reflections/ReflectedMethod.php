<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use ReflectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;

class ReflectedMethod extends \ReflectionMethod implements Reflected
{
    use ReflectedTrait;

    /**
     * implementing the getType() method in the Reflected Interface
     * @return ReflectionType|ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType
     */
    public function getType(): ReflectionType|ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType
    {
        return parent::getReturnType();
    }

    /**
     * implementing the getTypeFromDocBlock() method in the Reflected Interface
     * @return \phpDocumentor\Reflection\DocBlock\Tag
     */
    public function getTypeFromDocBlock(): ?string
    {
        return $this->getDocBlock()?->getTagsByName('return') [0];
    }

    /**
     * overriding the ReflectionMethod::getParameters($name) to return an array of ReflectedParameter
     * 
     * @return ReflectedParameter[]
     */
    public function getParameters(): array {

        $reflectedParaemters = [];

        foreach (parent::getParameters() as $parameter)
        {
            $reflectedParaemters[] = 
                new ReflectedParameter([$this->class, $this->getName()], $parameter->getName());
        }

        return $reflectedParaemters;
    }
}

