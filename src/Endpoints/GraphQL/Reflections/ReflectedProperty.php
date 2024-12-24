<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use phpDocumentor\Reflection\DocBlockFactoryInterface;


class ReflectedProperty extends \ReflectionProperty implements Reflected
{
    use ReflectedTrait;

    public function getType(): \ReflectionNamedType
    {
        return $this->getType();
    }


    public function getTypeFromDocBlock(DocBlockFactoryInterface $docBlockFactoryInterface): string|null
    {
        $docBlock = $docBlockFactoryInterface->create($this->getDocComment());
                        
        foreach ($docBlock->getTagsByName('var') as $parameterTag)
        {
            $docBlockType = $parameterTag->__tostring();

            if ($this->isList($docBlockType)) {
                return $docBlockType;
            }
        }

        return null;
    }

}