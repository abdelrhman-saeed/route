<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\GraphObjectBuilder;
use phpDocumentor\Reflection\DocBlock;


class ReflectedParameter extends \ReflectionParameter implements Reflected
{
    use ReflectedTrait;

    /**
     * overriding the setDocBlock() method in the ReflectedTrait trait
     * to get the DocBlock of the parameter's method
     * @return ?DocBlock
     */
    private function getDocBlock(): ?DocBlock
    {
        if (($methodDocComment = $this->getDeclaringFunction()->getDocComment()) == false) {
            return null;
        }

        return GraphObjectBuilder::getDocBlockFactoryInterface()
                    ->create($methodDocComment);
    }

    /**
     * overriding the getDocComment() method in the ReflectedTrait trait
     * to get the DocComment of the parameter from it's method
     * 
     * for example "@param T $param_name Description"
     * 
     * @return string|false
     */
    public function getDocComment(): string|false
    {
        foreach ($this->getDocBlock()?->getTagsByName('param') as $parameterTag)
        {
            if (str_contains($parameterTag->__tostring(), $this->getName())) {
                return $parameterTag->__tostring();
            }
        }

        return false;
    }

    /**
     * implementing the getTypeFromDocBlock() method in the Reflected Interface
     * @return string|null
     */
    public function getTypeFromDocBlock(): string|null
    {
        if (!$docComment = $this->getDocComment()) {
            return null;
        }
        
        return explode(' ', $docComment, 2)[0];
    }

    /**
     * implementing the getDescriptionFromDocBlock() method in the Reflected Interface
     * @return string|null
     */
    public function getDescriptionFromDocBlock(): ?string
    {
        if (!$docComment = $this->getDocComment()) {
            return null;
        }

        $docComment = explode(' ', $docComment, 3);

        if (! isset($docComment[2])) {
            return null;
        }

        return $docComment[2];
    }
}