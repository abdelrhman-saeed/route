<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObjectBuilder;
use phpDocumentor\Reflection\DocBlock;
use ReflectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;


trait ReflectedTrait
{
    /**
     * a DocBlock instance to fetch the DocComment
     * of the class properties, methods, parameters
     * @var  ?DocBlock
     */
    private ?DocBlock $docBlock = null;

    /**
     * implementing the getType() method in the Reflected Interface
     * @return ReflectionType|ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType
     */
    public function getType(): ReflectionType|ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType
    {
        return parent::getType();
    }

    /**
     * implementing the isList() method from the Reflected Interface
     * @param string $docblockType
     * @return bool
     */
    public function isList(string $docblockType): bool {
        return str_contains($docblockType, '[]');
    }

    /**
     * just a getter
     * @return \phpDocumentor\Reflection\DocBlock
     */
    private function getDocBlock(): ?DocBlock
    {
        if (($docComment = $this->getDocComment()) == false) {
            return null;
        }

        return GraphObjectBuilder::getDocBlockFactoryInterface()
                    ->create($docComment);
    }

    /**
     * implementing fthe getDescriptionFromDocBlock() method from Reflected Interface
     * @return string
     */
    public function getDescriptionFromDocBlock(): ?string
    {
        return $this->getDocBlock()?->getDescription()->__tostring();
    }
}