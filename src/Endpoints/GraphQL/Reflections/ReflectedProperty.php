<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;


class ReflectedProperty extends \ReflectionProperty implements Reflected
{
    use ReflectedTrait;

    /**
     * overriding the getDeclaringClass() method in the ReflectionProperty class
     * the return an instance of the ReflectedClass instead of ReflectionClass
     * 
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass
     */
    public function getDeclaringClass(): ReflectedClass {
        return new ReflectedClass($this->getDeclaringClass()->getName());
    }

    /**
     * implementing the getTypeFromDocBlock() method in the Reflected Interface
     * @return string|null
     */
    public function getTypeFromDocBlock(): string|null
    {
        return $this->getDocBlock()?->getTagsByName('var')[0]?->__tostring();
    }
}