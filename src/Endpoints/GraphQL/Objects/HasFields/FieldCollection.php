<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\EndpointCollection;
use AbdelrhmanSaeed\Route\API\GraphQL;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass;
use AbdelrhmanSaeed\Route\Resolvers\Resolver;


class FieldCollection extends EndpointCollection
{
    /**
     * Summary of groupedGQLObFieldsjects
     * @var Field[]
     */
    private array $groupedGQLObFieldsjects = [];

    /**
     * Summary of setMiddlewares
     * @param string[] $middlewares
     * @return FieldCollection
     */
    public function setMiddlewares(string ...$middlewares): static
    {
        return parent::setMiddlewares(...$middlewares)
                        ->applyMiddlewares();
    }

    private function applyMiddlewares(): static
    {
        if (! empty($this->groupedGQLObFieldsjects) && !is_null($this->middlewares)) {
            foreach ($this->groupedGQLObFieldsjects as $GQLFieldObject) {
                $GQLFieldObject->setMiddlewares(...$this->middlewares);
            }
        }

        return $this;
    }

    public function setResolverToEndpoints(): static
    {
        if (is_null($this->resolver)) {
            return $this;
        }

        $reflectedClass = new ReflectedClass($this->getResolver()->getAction());

        foreach ($this->groupedGQLObFieldsjects as $GQLFieldObject)
        {
            if (! $reflectedClass->hasMethod($GQLFieldObject->getReflected())) {
                throw new \Exception('');
            }

            $reflectedMethod = $reflectedClass->getMethod($GQLFieldObject->getReflected());

            $resolver = new Resolver([
                $reflectedClass->getName(),
                $GQLFieldObject->getResolver()->getAction()
            ]);

            $GQLFieldObject->setReflected($reflectedMethod)
                            ->setResolver($resolver);
        }

        return $this;
    }

    public function group(\Closure $callback): static
    {

        /**
         * @var <string, Field[]>
         */
        $GQLFieldsObjectsBkp = GraphQL::$rootGraphQLObjects;
        GraphQL::$rootGraphQLObjects = [];

        $callback();

        /**
         * @var Field[]
         */
        $this->groupedGQLObFieldsjects = array_merge(
            GraphQL::$rootGraphQLObjects[GraphQL::QUERY],
            GraphQL::$rootGraphQLObjects[GraphQL::MUTATION] ?? []
        );

        $this->setResolverToEndpoints()->applyMiddlewares();

        GraphQL::$rootGraphQLObjects[GraphQL::QUERY]
            = array_merge(GraphQL::$rootGraphQLObjects[GraphQL::QUERY], $GQLFieldsObjectsBkp[GraphQL::QUERY]);

        GraphQL::$rootGraphQLObjects[GraphQL::MUTATION]
            = array_merge(GraphQL::$rootGraphQLObjects[GraphQL::MUTATION] ?? [], $GQLFieldsObjectsBkp[GraphQL::MUTATION]) ?? [];

        return $this;
    }
}