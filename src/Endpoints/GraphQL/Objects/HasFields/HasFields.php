<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\{
    Objects\GraphObject,
    Objects\GraphObjectBuilder,
    Reflections\Reflected,
    Reflections\ReflectedEnum
};


abstract class HasFields extends GraphObject
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
        if (is_a($this->reflected, ReflectedEnum::class)) {
            return;
        }

        foreach ($this->reflected->getProperties() as $reflectedProperty)
        {
            if (empty($reflectedProperty->getAttributes(Field::class))) {
                continue;
            }

            $this->config['fields'][]
                = self::getReflectedMetaData($reflectedProperty);
        }
    }
}