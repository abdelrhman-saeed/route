<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;

use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionAttribute;


/**
 * Summary of Reflected
 */
interface Reflected
{
    /**
     * Summary of getType
     * @return \ReflectionNamedType
     */
    public function getType(): \ReflectionNamedType;

    /**
     * Summary of getTypeFromDocBlock
     * @param \phpDocumentor\Reflection\DocBlockFactoryInterface $docBlockFactoryInterface
     * @return null|string
     */
    public function getTypeFromDocBlock(DocBlockFactoryInterface $docBlockFactoryInterface): ?string;

    /**
     * Summary of isList
     * @param string $docblock
     * @return bool
     */
    public function isList(string $docblock): bool;

    /**
     * @param mixed $name
     * @param int $flags
     * @return ReflectionAttribute[]
     */
    public function getAttributes(?string $name = null, int $flags = 0): array;

    public function getName(): string;
}