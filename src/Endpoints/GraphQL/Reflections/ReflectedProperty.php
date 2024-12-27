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


    public function getTypeFromDocBlock(DocBlockFactoryInterface $docBlockFactoryInterface): string|null
    {
        // $docBlockTags = $docBlockFactoryInterface->create($this->getDocComment())
        //                 ->getTagsByName('var') [0];

        return $docBlockFactoryInterface->create($this->getDocComment())
                    ->getTagsByName('var')[0]?->__tostring();
                        
    }

}