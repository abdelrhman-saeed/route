<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Exceptions\WrongSchemaDefinition;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;


class GraphObjectBuilder
{
    private static DocBlockFactoryInterface $docBlockFactoryInterface;
    /**
     * Cache created BaseGraphObject Instances
     * 
     * @var BaseGraphObject[]
     */
    private static array $cachedGraphObjects = [];

    /**
     * Set the value of docBlockFactoryInterface
     *
     * @param DocBlockFactoryInterface $docBlockFactoryInterface
     */
    public static function setDocBlockFactoryInterface(DocBlockFactoryInterface $docBlockFactoryInterface): void {
        self::$docBlockFactoryInterface = $docBlockFactoryInterface;
    }

    private static function wrapWithNeededObjects(Reflected $reflected, BaseGraphObject $baseGraphObject, bool $isList): BaseGraphObject
    {
        if ($isList) {
            $baseGraphObject = new ListedObject($baseGraphObject);
        }

        if (!$reflected->getType()->allowsNull()) {
            $baseGraphObject = new NotNull($baseGraphObject);
        }

        return $baseGraphObject;
    }


    /**
     * Creates or Retrive GraphQL Objects Based on the given reflection
     * 
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected $reflected
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\BaseGraphObject
     */
    public static function build(Reflected $reflected): BaseGraphObject
    {
        /**
         * @var string
         */
        $typeFromDocBlock   = $reflected->getTypeFromDocBlock(self::$docBlockFactoryInterface);
        $isList             = false;

        if (($typeName = $reflected->getType()->getName()) == 'array'
                && !$isList = $reflected->isList($typeFromDocBlock))
        {
            throw new WrongSchemaDefinition('anything type hinted as an array
                        should have a docblock defining the return type such as T[]'
                    );
        }

        if ($isList) {
            $typeName = str_replace('[]', '', $typeFromDocBlock);
        }

        if (isset(self::$cachedGraphObjects[$typeName])) {
            $graphObject = self::$cachedGraphObjects[$typeName];
        }

        if (! is_null(ScalarObject::scalars($typeName))) {
            $graphObject = new ScalarObject($typeName);
        }

        else
        {
            foreach ((new ReflectedClass($typeName))->getAttributes()
                        as $attribute)
            {
                if (is_a($attribute, BaseGraphObject::class))
                {
                    self::$cachedGraphObjects[$typeName]
                        = $graphObject
                        = $attribute->newInstance()->setReflection($reflected);

                    break;
                }
            }

        }

        return self::wrapWithNeededObjects($reflected, $graphObject, $isList);

    }


}