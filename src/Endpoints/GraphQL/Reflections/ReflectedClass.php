<?php


namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use ReflectionProperty;

class ReflectedClass extends \ReflectionClass
{
    /**
     * overriding the ReflectionClass::getMethod($name) to return an instance of ReflectedMethod
     * 
     * @param string $name
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedMethod
     */
    public function getMethod(string $name) : ReflectedMethod {
        return new ReflectedMethod($this->getName(), $name);
    }

    /**
     * overriding the ReflectionClass::getMethods($filter) to return an array of ReflectedMethod
     * 
     * @param int|null $filter
     * @return ReflectedMethod[]
     */
    public function getMethods(int|null $filter = null): array
    {
        $reflectedMethods = [];

        foreach (parent::getMethods($filter) as $method) {
            $reflectedMethods[] = $this->getMethod($method->getName());
        }

        return $reflectedMethods;
    }

    /**
     * overriding the ReflectionClass::properties($filter) to return an array of ReflectedProperty
     * 
     * @param int|null $filter
     * @return ReflectedProperty[]
     */
    public function getProperties(int|null $filter = null): array
    {
        $reflectedProperties = [];

        foreach (parent::getProperties($filter) as  $property) {
            $reflectedProperties[] = $this->getProperty($property->getName());
        }

        return $reflectedProperties;
    }

    /**
     * overriding the ReflectionClass::properties($filter) to return an instance of ReflectedProperty
     * 
     * @param string $name
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedProperty
     */
    public function getProperty(string $name): ReflectedProperty {
        return new ReflectedProperty($this->getName(), $name);
    }
}