<?php

namespace AbdelrhmanSaeed\Route\API;


use phpDocumentor\Reflection\DocBlockFactory;

use GraphQL\Type\{
    Definition\ObjectType, Schema, SchemaConfig
};

use GraphQL\{
    Error\DebugFlag, GraphQL as GraphService,
};

use Symfony\Component\HttpFoundation\{
    Request, Response
};

use AbdelrhmanSaeed\Route\{
    Resolvers\Resolver,
    Exceptions\WrongRoutePatternException,
    Endpoints\GraphQL\Objects\GraphObjectBuilder,
    Endpoints\GraphQL\Reflections\ReflectedClass,
    Endpoints\GraphQL\Objects\HasFields\Field,
    Endpoints\GraphQL\Objects\HasFields\FieldCollection
};


class GraphQL extends API
{
    /**
     * GraphQL Schema
     * @var Schema
     */
    private static Schema $schema;
    private static ?SchemaConfig $schemaConfig;
    private static int $debugFlag = DebugFlag::NONE;
    public CONST QUERY      = 'Query';
    public CONST MUTATION   = 'Mutation';
    
    /**
     * the GraphQL endpoint
     * @var string
     */
    private static ?string $endpoint = null;

    /**
     * @return void
     */
    public static function debug(): void {
        self::$debugFlag = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;
    }

    /**
     * GraphQL endpoint Setter
     * @param string $name
     * @return void
     */
    public static function setEndpoint(string $name): void {
        self::$endpoint = $name;
    }

    /**
     * associative array containing GraphQL root Object names as keys
     * and GraphQL Objects fields as values
     * 
     * @var <string,Field[]>
     */
    public static array $rootGraphQLObjects = ['Query' => [], 'Mutation' => []];

    /**
     * @return \GraphQL\Type\SchemaConfig
     */
    public static function getSchemaConfig(): SchemaConfig {
        return self::$schemaConfig ?? self::$schemaConfig = new SchemaConfig;
    }

    /**
     * generates GraphQL Object field config and add it to one of the root GraphQL Objects
     * 
     * @param string $method
     * @param string $controller
     * @param string $rootObject
     * 
     * @return Field
     */
    private static function addFieldToRootObject(string $rootObject, string $method, null|string $controller = null): Field
    {
        $resolver = new Resolver($method);

        /**
         * @var ReflectedClass|null
         */
        static $reflected = null;

        if (! is_null($controller))
        {
            if (is_null($reflected) || $reflected->getName() !== $controller) {
                $reflected = new ReflectedClass($controller);
            }

            $reflected = $reflected->getMethod($method);
            $resolver->setAction([$controller, $method]);
        }

        return self::$rootGraphQLObjects[$rootObject][] =
                new Field($resolver, $reflected ?? $method);
    }

    /**
     * uses GraphQL::addFieldToRootObject() to add fields to the root GraphQL QUERY Object
     * 
     * @param string $method
     * @param ?string $controller
     * @return Field
     */
    public static function query(null|string $method = null, null|string $controller = null): Field {
        return self::addFieldToRootObject(self::QUERY, $method, $controller);
    }

    /**
     * uses GraphQL::addFieldToRootObject() to add fields to the root GraphQL QUERY Object
     * 
     * @param string $method
     * @param null|string $controller
     * @return Field
     */
    public static function mutation(null|string $method = null, null|string $controller = null): Field {
        return self::addFieldToRootObject( self::MUTATION, $method, $controller);
    }

    public static function setController(string $controller): FieldCollection {
        return new FieldCollection(resolver: new Resolver($controller));
    }

    public static function setMiddlewares(string ...$middlewares): FieldCollection {
        return (new FieldCollection())->setMiddlewares(...$middlewares);
    }
    public static function resource(string $name, string $controller): FieldCollection
    {
        return self::setController($controller)
                ->group(function () use ($name): void {

                    $name = ucfirst($name);
                    $queries = [
                        "save"  => "mutation", "update" => "mutation", "delete" => "mutation",
                        "index" => "query", "show"      => "query",
                    ];

                    foreach ($queries as $controllerMethod => $GQLMethod) {
                        self::{$GQLMethod}("$controllerMethod$name");
                    }
                });
    }
    /**
     * handles the incoming GraphQL request
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    protected static function handle(Request $request, Response $response): void
    {
        $requestContent = json_decode($request->getContent());

        $result = GraphService::executeQuery(
                    schema: self::$schema,
                    source: $requestContent->query,
                    variableValues: $requestContent->variables ?? null)
                ->toArray(self::$debugFlag);

        $response->headers->add(['Content-Type' => 'application/json']);
        $response->setContent(json_encode($result))->send();   
    }

    private static function triggerFieldObjectsGenerators(): void
    {
        self::$rootGraphQLObjects[self::QUERY] =
            array_map(
                fn (Field $field) => $field->getConfig(),
                self::$rootGraphQLObjects[self::QUERY]
            );

        self::$rootGraphQLObjects[self::MUTATION] =
            array_map(
                fn (Field $field) => $field->getConfig(),
                self::$rootGraphQLObjects[self::MUTATION]
            );
    }

    private static function instanciateGQLRootObject(string $name): null|ObjectType
    {
        return new ObjectType([
            'name'      => $name,
            'fields'    => self::$rootGraphQLObjects[$name]
        ]);
    }

    /**
     * @param string $endpoints
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    public static function setup(string $endpoints, Request $request, Response $response): void
    {
        if (is_null(self::$endpoint)) {
            throw new WrongRoutePatternException('GraphQL endpoint must be defined before GraphQL setup');
        }

        Route::any(self::$endpoint, function ()
                use ($endpoints, $request, $response): void
            {
                GraphObjectBuilder::setDocBlockFactoryInterface(DocBlockFactory::createInstance());

                self::includeEndpoints($endpoints);
                self::triggerFieldObjectsGenerators();

                $query      = self::instanciateGQLRootObject(self::QUERY);
                $mutation   = self::instanciateGQLRootObject(self::MUTATION);

                $schemaConfig = self::getSchemaConfig();

                $schemaConfig->setQuery($query)
                                ->setMutation($mutation);


                self::$schema = new Schema($schemaConfig);

                self::handle($request, $response);
        });
    }


}
