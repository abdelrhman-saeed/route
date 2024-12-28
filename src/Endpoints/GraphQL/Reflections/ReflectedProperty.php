<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use phpDocumentor\Reflection\DocBlockFactoryInterface;


class ReflectedProperty extends \ReflectionProperty implements Reflected
{
    use ReflectedTrait;

    public function getType(): \ReflectionNamedType
    {
        return parent::getType();
    }

    public function getDeclaringClass(): ReflectedClass {
        return new ReflectedClass($this->getDeclaringClass()->getName());
    }

    public function getTypeFromDocBlock(DocBlockFactoryInterface $docBlockFactoryInterface): string|null
    {
        return $docBlockFactoryInterface->create($this->getDocComment())
                    ->getTagsByName('var')[0]?->__tostring();
                        
    }

}