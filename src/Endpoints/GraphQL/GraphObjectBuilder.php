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
            $typeName   = $reflected->getTypeFromDocBlock();
            $isList     = $reflected->isList($typeName);
        }

        if ($isList) {
            $typeName = str_replace('[]', '', $typeName);
        }

        if (isset(self::$cachedGraphObjects[$typeName])) {
            $graphObject = self::$cachedGraphObjects[$typeName];
        }

        if (! is_null(ScalarObject::scalars($typeName))) {
            $graphObject = new ScalarObject($typeName);
        }

        else
        {
            foreach (($reflectedClass = new ReflectedClass($typeName))->getAttributes()
                        as $attribute)
            {
                $attribute = $attribute->newInstance();

                if (is_a($attribute, BaseGraphObject::class))
                {

                    $attribute->setReflection($reflectedClass);
                    $graphObject = self::$cachedGraphObjects[$attribute->getConfig('name')] = $attribute;

                    break;
                }
            }

        }

        return self::wrapWithNeededObjects($reflected, $graphObject, $isList);

    }


}