<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL;

use GraphQL\Type\Definition\Type;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass;


abstract class BaseGraphObject
{
    /**
     * Summary of reflection
     * @var 
     */
    protected ReflectedClass $reflected;

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

    public function setReflection(ReflectedClass $reflected): self
    {
        $this->reflected = $reflected;

        $this->config['name']
            ?? $this->config['name'] = $reflected->getShortName();

        if (! ($docComment = $reflected->getDocComment()))
        {
            $this->config['description']
                = GraphObjectBuilder::getDocBlockFactoryInterface()
                        ->create($docComment) ->getDescription();
        }

        return $this;
    }

    /**
     * Summary of build
     * @return \GraphQL\Type\Definition\Type
     */

    abstract public function build(): Type;
}