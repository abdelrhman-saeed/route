<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use GraphQL\Type\Definition\Type;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\{
    Reflected, ReflectedEnum, ReflectedClass
};


abstract class GraphObject
{
    /**
     * Summary of reflection
     * @var 
     */
    protected Reflected|ReflectedEnum|ReflectedClass $reflected;

    /**
     * @var mixed[]
     */
    protected array $config;

    /**
     * Summary of type
     * @var 
     */
    protected ?Type $type = null;


    public function getConfig(string $config): mixed {
        return $this->config[$config] ?? null;
    }

    public function setReflection(ReflectedClass|ReflectedEnum $reflected): self
    {
        $this->reflected = $reflected;

        $this->config['name']
            ?? $this->config['name'] = $reflected->getShortName();

        if (($docComment = $reflected->getDocComment()) !== false)
        {
            $this->config['description'] = GraphObjectBuilder::getDocBlockFactoryInterface()
                    ->create($docComment)->getDescription()
                    ->__tostring();
        }

        return $this;
    }

    /**
     * Summary of build
     * @return \GraphQL\Type\Definition\Type
     */

    abstract public function build(): Type;
}