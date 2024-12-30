<?php

namespace AbdelrhmanSaeed\Route\API;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\GraphObjectBuilder;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\HasFields\OutputObject;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Symfony\Component\HttpFoundation\{Request, Response};
use GraphQL\GraphQL as GraphService;
use phpDocumentor\Reflection\DocBlockFactory;
use GraphQL\Error\DebugFlag;


class GraphQL extends API
{
    private static ?Schema $schema;
    public CONST QUERY      = 'Query';
    public CONST MUTATION   = 'Mutation';
    private static array $rootGraphQLObjects = ['Query' => [], 'Mutation' => []];

    public static function query(string $controller, string $method): void
    {
        self::addFieldToRootObject($controller, $method, self::QUERY);
    }

    public static function mutation(string $controller, string $method): void
    {
        self::addFieldToRootObject($controller, $method, self::MUTATION);
    }

    public static function addFieldToRootObject(string $controller, string $method, string $rootObject): void
    {

        $reflectedMethod = (new ReflectedClass($controller))->getMethod($method);

        self::$rootGraphQLObjects[$rootObject][] =
            OutputObject::setupResolvedField($reflectedMethod);
    }

    public static function handle(Request $request, Response $response): void
    {
        $requestContent = json_decode($request->getContent());

        $result = GraphService::executeQuery(

                    self::$schema,
                    $requestContent->query,
                    null,
                    null,
                    $requestContent->variables ?? null)

                ->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE);

        $response->headers->add(['Content-Type' => 'application/json']);

        $response->setContent(json_encode($result))->send();   
    }

    public static function setup(string $endpoints, Request $request, Response $response): void
    {
        GraphObjectBuilder::setDocBlockFactoryInterface(DocBlockFactory::createInstance());

        self::includeEndpoints($endpoints);

        // $query      = new Query( self::$rootGraphQLObjects[self::QUERY] );
        // $mutation   = new Mutation( self::$rootGraphQLObjects[self::MUTATION] );
        
        // print_r(self::$rootGraphQLObjects[self::QUERY]);
        // exit;
        // /**
        //  * @var null|ObjectType
        //  */
        $query = new ObjectType([
            'name'      => 'Query',
            'fields'    => self::$rootGraphQLObjects[self::QUERY]
        ]);

        // /**
        //  * @var null|ObjectType
        //  */
        // $mutation = new ObjectType([
        //     'name'      => 'Mutation',
        //     'fields'    => self::$rootGraphQLObjects[self::MUTATION]
        // ]);

        ($schemaConfig = new SchemaConfig)
                                ->setQuery($query);
                                // ->setMutation($mutation);

        self::$schema  = new Schema($schemaConfig);

        self::handle($request, $response);
    }


}
