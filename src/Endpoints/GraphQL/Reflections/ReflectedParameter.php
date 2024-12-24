<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use phpDocumentor\Reflection\DocBlockFactoryInterface;


class ReflectedParameter extends \ReflectionParameter implements Reflected
{
    use ReflectedTrait;

    public function getType(): \ReflectionNamedType
    {
        // $this->__construct();
        return $this->getType();
    }


    public function getTypeFromDocBlock(DocBlockFactoryInterface $docBlockFactoryInterface): string|null
    {

        $docBlock = $docBlockFactoryInterface
                        ->create($this->getDeclaringFunction()->getDocComment());
                        
        foreach ($docBlock->getTagsByName('param') as $parameterTag)
        {
            $docBlockType = $parameterTag->__tostring();

            if (str_contains($docBlockType, $this->getName())
                && $this->isList($docBlockType))
            {
                return explode(' ', $docBlockType)[0];
            }
        }

        return null;
    }

}