<?php

namespace AbdelrhmanSaeed\Route\API;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\{
    Objects\GraphObjectBuilder, Objects\HasFields\Output, Reflections\ReflectedClass
};

use GraphQL\Type\{
    Definition\ObjectType, Schema, SchemaConfig
};

use GraphQL\{
    Error\DebugFlag, GraphQL as GraphService,
};

use Symfony\Component\HttpFoundation\{
    Request, Response
};

use phpDocumentor\Reflection\DocBlockFactory;


class GraphQL extends API
{
    /**
     * GraphQL Schema
     * @var Schema
     */
    private static Schema $schema;

    /**
     * GraphQL SchemaConfig
     * @var 
     */
    private static ?SchemaConfig $schemaConfig;

    public CONST QUERY      = 'Query';

    public CONST MUTATION   = 'Mutation';

    /**
     * associative array containing GraphQL root Object names as keys
     * and GraphQL Objects fields as values
     * 
     * @var <string, string[]>
     */
    private static array $rootGraphQLObjects = ['Query' => [], 'Mutation' => []];

    /**
     * @return \GraphQL\Type\SchemaConfig
     */
    public static function getSchemaConfig(): SchemaConfig {
        return self::$schemaConfig ?? self::$schemaConfig = new SchemaConfig;
    }

    /**
     * generates GraphQL Object field config and add it to one of the root GraphQL Objects
     * 
     * @param string $controller
     * @param string $method
     * @param string $rootObject
     * 
     * @return void
     */
    private static function addFieldToRootObject(string $controller, string $method, string $rootObject): void
    {

        $reflectedMethod = (new ReflectedClass($controller))->getMethod($method);

        self::$rootGraphQLObjects[$rootObject][] =
            Output::setupResolvedField($reflectedMethod);
    }

    /**
     * uses GraphQL::addFieldToRootObject() to add fields to the root GraphQL QUERY Object
     * 
     * @param string $controller
     * @param string $method
     * @return void
     */
    public static function query(string $controller, string $method): void
    {
        self::addFieldToRootObject($controller, $method, self::QUERY);
    }

    /**
     * uses GraphQL::addFieldToRootObject() to add fields to the root GraphQL QUERY Object
     * 
     * @param string $controller
     * @param string $method
     * @return void
     */
    public static function mutation(string $controller, string $method): void
    {
        self::addFieldToRootObject($controller, $method, self::MUTATION);
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
                    variableValues: $requestContent->variables ?? null
                )->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);

        $response->headers->add(['Content-Type' => 'application/json']);
        $response->setContent(json_encode($result))->send();   
    }

    /**
     * @param string $endpoints
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    public static function setup(string $endpoints, Request $request, Response $response): void
    {
        GraphObjectBuilder::setDocBlockFactoryInterface(DocBlockFactory::createInstance());

        self::includeEndpoints($endpoints);

        /**
         * @var null|ObjectType
         */
        $query = new ObjectType([
            'name'      => 'Query',
            'fields'    => self::$rootGraphQLObjects[self::QUERY]
        ]);

        /**
         * @var null|ObjectType
         */
        $mutation = new ObjectType([
            'name'      => 'Mutation',
            'fields'    => self::$rootGraphQLObjects[self::MUTATION]
        ]);

        $schemaConfig = self::getSchemaConfig();

        $schemaConfig->setQuery($query)
                        ->setMutation($mutation);

        self::$schema = new Schema($schemaConfig);

        self::handle($request, $response);
    }
}
