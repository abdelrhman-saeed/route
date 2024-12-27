<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;


trait ReflectedTrait
{
    public function isList(string $docblockType): bool {
        return str_contains($docblockType, '[]');
    }
}