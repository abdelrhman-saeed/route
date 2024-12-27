<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use phpDocumentor\Reflection\DocBlockFactoryInterface;


class ReflectedParameter extends \ReflectionParameter implements Reflected
{
    use ReflectedTrait;

    public function getType(): \ReflectionNamedType
    {
        return parent::getType();
    }


    public function getTypeFromDocBlock(DocBlockFactoryInterface $docBlockFactoryInterface): string|null
    {

        $docBlock = $docBlockFactoryInterface
                        ->create($this->getDeclaringFunction()->getDocComment());
                        
        foreach ($docBlock->getTagsByName('param') as $parameterTag)
        {
            if (str_contains($parameterTag->__tostring(), $this->getName())) {
                return explode(' ', $parameterTag->__tostring())[0];
            }
        }

        return null;
    }

}