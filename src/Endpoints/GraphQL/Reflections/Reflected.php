<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use ReflectionAttribute;
use ReflectionIntersectionType;
use ReflectionUnionType;
use ReflectionType;
use ReflectionNamedType;


/**
 * Summary of Reflected
 */
interface Reflected
{
    /**
     * return ReflectionNamedType Object
     * wither it's a method, parameter or a property
     * 
     * @return ReflectionType|ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType;
     */
    public function getType(): ReflectionType|ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType;

    /**
     * just like getType() but it returns
     * the type name from the DocBlock
     * 
     * @return null|string
     */
    public function getTypeFromDocBlock(): ?string;

    /**
     * check if the DocBlock Type is an array or not
     * for example it checks if this type "string[]" is an array or not
     * 
     * @param string $docblock
     * @return bool
     */
    public function isList(string $docblock): bool;

    /**
     * ReflectedMethod, ReflectedParameter, and ReflectedProperty
     * has the getAttributes() method, but since i am dealing
     * with the Reflected Interface to achive polymorphism
     * i have to define the getAttributes() method here
     * 
     * @param mixed $name
     * @param int $flags
     * @return ReflectionAttribute[]
     */
    public function getAttributes(?string $name = null, int $flags = 0): array;

    /**
     * same as getAttributes() method
     * @return string
     */
    public function getName(): string;
    
    /**
     * returns the DocBlock Comment of a property or a method
     * if it's a parameter then will return the parameter definition
     * from the method DocBlock for example "@param string $param_name Description"
     * 
     * @return string|false
     */
    public function getDocComment(): string|false;

    /**
     * it uses the getDocComment() to fetch the description from
     * @return void
     */
    public function getDescriptionFromDocBlock(): ?string;
}