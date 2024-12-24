<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections;


trait ReflectedTrait
{
    public function isList(string &$docblockType): bool
    {
        if (str_contains($docblockType, '[]'))
        {
            $docblockType = str_replace('[]', '', $docblockType);
            return true;
        }

        return false;
    }
}