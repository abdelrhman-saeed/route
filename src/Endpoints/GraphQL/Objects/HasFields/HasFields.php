<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObject;


abstract class HasFields extends GraphObject
{
    protected function setupFields(): void
    {
        $this->config['fields'] = [];
        
        foreach (
            array_merge($this->reflected->getMethods(), $this->reflected->getProperties())
                as $reflected)
        {
            if (empty($reflected->getAttributes(Field::class))) {
                continue;
            }

            $this->config['fields'][] = (new Field(null, $reflected))->getConfig();
        }
    }
}