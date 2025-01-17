<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedClass;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedEnum;
use GraphQL\Type\Definition\ResolveInfo;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use AbdelrhmanSaeed\Route\API\GraphQL;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\Scalar;
use ReflectionUnionType;


class GraphObjectBuilder
{
    private static DocBlockFactoryInterface $docBlockFactoryInterface;

    /**
     * Cache created GraphObject Instances
     * 
     * @var GraphObject[]
     */
    private static array $cachedGraphObjects = [];

    public static function getCachedObject(string $name): ?GraphObject {
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
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObject $graphObject
     * @param bool $isList
     * 
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObject
     */
    private static function wrapWithNeededObjects(Reflected $reflected, GraphObject $graphObject, bool $isList): GraphObject {

        if ($isList)
            $graphObject = new Listed($graphObject);

        if (!$reflected->getType()->allowsNull())
            $graphObject = new NotNull($graphObject);

        return $graphObject;
    }


    /**
     * Creates or Retrive GraphQL Objects Based on the given reflection
     * 
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\Reflected $reflected
     * @return \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObject
     */
    public static function build(Reflected $reflected): GraphObject
    {
        $isList = false;
        $graphObject = null;

        // union
        if (is_a($reflected->getType(), ReflectionUnionType::class))
        {
            foreach (($types = $reflected->getType()->getTypes()) as $key => $type) {
                $types[$key] = self::getGraphObjectByName($type->getName())->build();
            }

            self::$cachedGraphObjects[]
                = $graphObject = new Union($types, $reflected);
        }

        // listed
        elseif (($typeName = $reflected->getType()->getName()) == 'array')
        {
            if ($isList = $reflected->isList($typeName = $reflected->getTypeFromDocBlock())) {
                $typeName = str_replace('[]', '', $typeName);
            }
        }

        // scalar
        elseif (!is_null(Scalar::scalars($typeName))) {
            $graphObject = new Scalar($typeName);
        }

        // Just a GraphObject
        else { $graphObject = self::getGraphObjectByName($typeName); }

        // wrap with listed and not nullable objects
        return self::wrapWithNeededObjects($reflected, $graphObject, $isList);
    }

    public static function getGraphObjectByName(string $name): ?GraphObject
    {
        $reflectedClass = new ReflectedClass($name);

        if ($reflectedClass->isEnum()) {
            $reflectedClass = new ReflectedEnum($name);
        }

        if (isset(self::$cachedGraphObjects[lcfirst($reflectedClass->getShortName())])) {
            return self::$cachedGraphObjects[lcfirst($reflectedClass->getShortName())];
        }

        return self::getGraphObjectByReflection($reflectedClass);

    }

    private static function getGraphObjectByReflection(ReflectedEnum|ReflectedClass $reflectedClass): ?GraphObject
    {
        foreach ($reflectedClass->getAttributes() as $attribute)
        {
            $attribute = $attribute->newInstance();

            if (is_a($attribute, GraphObject::class))
            {
                return self::$cachedGraphObjects[lcfirst($reflectedClass->getShortName())]
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