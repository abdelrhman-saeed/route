<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use phpDocumentor\Reflection\DocBlockFactoryInterface;


class ReflectedMethod extends \ReflectionMethod implements Reflected
{
    use ReflectedTrait;

    public function getType(): \ReflectionNamedType
    {
        return $this->getReturnType();
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

    public function getTypeFromDocBlock(DocBlockFactoryInterface $docBlockFactoryInterface): ?string
    {

        $docBlock = $docBlockFactoryInterface->create($this->getDocComment());

        if (is_null($returnType = $docBlock->getTagsByName('return')[0])) {
            return null;
        }

        $this->isList($returnType);

        return $returnType;
    }
}

