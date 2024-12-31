<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\BaseGraphObject;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\GraphObjectBuilder;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected;


abstract class HasFields extends BaseGraphObject
{
    protected static function getReflectedMetaData(Reflected $reflected): array
    {
        return [
            'name'          => $reflected->getName(),
            'description'   => $reflected->getDescriptionFromDocBlock(),
            'type'          => GraphObjectBuilder::build($reflected)->build()
        ];
    }

    protected function setupFieldsFromProperties(): void
    {
        foreach ($this->reflected->getProperties() as $reflectedPropoerty)
        {
            if (empty($reflectedPropoerty->getAttributes(Field::class))) {
                continue;
            }

            $this->config['fields'][]
                = self::getReflectedMetaData($reflectedPropoerty);
        }
    }
}