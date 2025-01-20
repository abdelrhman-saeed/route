<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedMethod;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\GraphObjectBuilder;
use GraphQL\Type\Definition\ResolveInfo;


class OutputResolver
{
    public static function getResolver(ReflectedMethod $reflectedMethod, Object $object): \Closure
    {
        return function (mixed $objectValue, array $fieldArgs = [], mixed $context = null, null|ResolveInfo $resolveInfo = null)

                    use ($reflectedMethod, $object): mixed
                    {
                        foreach ($fieldArgs as $fieldName => $fieldValue) {

                            if (! is_null($graphObject = GraphObjectBuilder::getCachedObject($fieldName))) {

                                $objectName = $graphObject->getReflection()->getName();

                                if ($graphObject->getReflection()->isEnum())
                                {
                                    if ($graphObject->getReflection()->isBacked()) {
                                        $fieldArgs[$fieldName] = $objectName::tryfrom($fieldValue);
                                    }

                                    else {
                                        foreach ($objectName::cases() as $case) {
                                            if ($case->name == $fieldValue) {
                                                $fieldArgs[$fieldName] = $case;
                                            }
                                        }
                                    }

                                    continue;
                                }

                                $fieldArgs[$fieldName] = new $objectName;

                                foreach($fieldValue as $argName => $value)
                                {
                                    $setter = 'set' . ucfirst($argName);

                                    method_exists($fieldArgs[$fieldName], $setter)
                                        ? $fieldArgs[$fieldName]->{$setter} ($value)
                                        : $fieldArgs[$fieldName]->{$argName} = $value;
                                }
                            }
                        } 

                        if (! empty($reflectedParameters = $reflectedMethod->getParameters())
                                && $reflectedParameters[0]->getType()->getName() == ResolveInfo::class) {

                            array_unshift($fieldArgs, $resolveInfo);
                        }
                            // array_unshift($fieldArgs, $resolveInfo);

                        return $reflectedMethod->invoke($object, ...$fieldArgs);
                    };
    }
}