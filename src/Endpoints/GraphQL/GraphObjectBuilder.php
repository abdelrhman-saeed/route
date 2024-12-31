<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use AbdelrhmanSaeed\Route\API\GraphQL;


class GraphObjectBuilder
{
    private static DocBlockFactoryInterface $docBlockFactoryInterface;
    /**
     * Cache created BaseGraphObject Instances
     * 
     * @var BaseGraphObject[]
     */
    private static array $cachedGraphObjects = [];

    public static function getCachedObject(string $name): ?BaseGraphObject {
        return self::$cachedGraphObjects[$name] ?? null;
    }
    /**
     * Set the value of docBlockFactoryInterface
     *
     * @param DocBlockFactoryInterface $docBlockFactoryInterface
     */
    public static function setDocBlockFactoryInterface(DocBlockFactoryInterface $docBlockFactoryInterface): void {
        self::$docBlockFactoryInterface = $docBlockFactoryInterface;
    }

    /**
     * Summary of getDocBlockFactoryInterface
     * @return \phpDocumentor\Reflection\DocBlockFactoryInterface
     */
    public static function getDocBlockFactoryInterface(): DocBlockFactoryInterface {
        return self::$docBlockFactoryInterface;
    }

    /**
     * Summary of wrapWithNeededObjects
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected $reflected
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\BaseGraphObject $baseGraphObject
     * @param bool $isList
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\BaseGraphObject
     */
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
    //  * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected $reflected
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\BaseGraphObject
     */
    public static function build(Reflected $reflected): BaseGraphObject
    {
        $isList = false;

        if (($typeName = $reflected->getType()->getName()) == 'array')
        {
            if ($isList = $reflected->isList($typeName = $reflected->getTypeFromDocBlock())) {
                $typeName = str_replace('[]', '', $typeName);
            }
        }

        $graphObject = null;

        if (!is_null(ScalarObject::scalars($typeName))) {
            $graphObject = new ScalarObject($typeName);
        }

        if (is_null($graphObject)) {
            $graphObject = self::getGraphObjectByName($typeName);
        }

        return self::wrapWithNeededObjects($reflected, $graphObject, $isList);

    }

    public static function getGraphObjectByName(string $name): ?BaseGraphObject
    {
        $reflectedClass = new ReflectedClass($name);

        if (isset(self::$cachedGraphObjects[$reflectedClass->getShortName()])) {
            return self::$cachedGraphObjects[$reflectedClass->getShortName()];
        }

        return self::getGraphObjectByReflection($reflectedClass);

    }

    private static function getGraphObjectByReflection(ReflectedClass $reflectedClass): ?BaseGraphObject
    {
        foreach ($reflectedClass->getAttributes() as $attribute)
        {
            $attribute = $attribute->newInstance();

            if (is_a($attribute, BaseGraphObject::class))
            {
                return self::$cachedGraphObjects[$reflectedClass->getShortName()]
                            = $attribute->setReflection($reflectedClass);
            }
        }

        return null;
    }

    /**
     * register GraphQL Object classes
     * @param string[] $classes
     * @return void
     */
    public static function register(array $classes): void
    {
        $classes = array_map(
            fn (string $class) => self::getGraphObjectByName($class)->build(),
            $classes
        );

        GraphQL::getSchemaConfig()->setTypes($classes);
    }
}