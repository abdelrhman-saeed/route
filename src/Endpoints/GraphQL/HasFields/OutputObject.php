<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\HasFields;

use GraphQL\Type\Definition\{
    ObjectType,
    Type,
    ResolveInfo
};

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\{
    GraphObjectBuilder,
    Reflections\ReflectedMethod,
    HasFields\HasFields
};

use Attribute;


#[Attribute(Attribute::TARGET_CLASS)]
class OutputObject extends HasFields
{

    /**
     * Summary of setupResolvedField
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedMethod $reflectedMethod
     * @return array
     */
    public static function setupResolvedField(ReflectedMethod $reflectedMethod): array {
        
        $reflectedParameters = $reflectedMethod->getParameters();
        $args = [];

        /**
         * GraphObjectBuilder::class only resolves Objects of BaseGraphObject class and scalar types
         * starting from index '1' to skip resolving the first method argument
         * of type ResolveInfo::class
         */
        for($i = 1; $i < count($reflectedParameters); $i++) {
            $args[] = self::getReflectedMetaData($reflectedParameters[$i]);
        }

        $field = self::getReflectedMetaData($reflectedMethod);

        $field['args'] = $args;
        $field['resolve'] = self::getResolver($reflectedMethod);

        return $field;
    }

    /**
     * Summary of getResolver
     * @param \AbdelrhmanSaeed\Route\Endpoints\GraphQL\Reflections\ReflectedMethod $reflectedMethod
     * @return \Closure
     */
    private static function getResolver(ReflectedMethod $reflectedMethod): \Closure
    {
        static $object = null;

        if (!is_a($object, $reflectedMethod->class)) {
            $object = new $reflectedMethod->class;
        }

        return function ( mixed $objectValue, array $args = [], mixed $context, ResolveInfo $resolveInfo)

                    use ($reflectedMethod, $object): mixed
                    {
                        foreach ($args as $k => $arg)
                        {
                            if (! is_null($graphObject = GraphObjectBuilder::getCachedObject($k)))
                            {
                                $args[$k] = new ($graphObject->reflected->getName());

                                foreach($arg as $argName => $value)
                                {
                                    $setter = 'set' . ucfirst($argName);

                                    method_exists($args[$k], $setter)
                                        ? $args[$k]->{$setter} ($value)
                                        : $args[$k]->{$argName} = $value;
                                }
                            }
                        } 

                        array_unshift($args, $resolveInfo);

                        return $reflectedMethod->invoke($object, ...$args);
                    };
    }

    public function build(): Type
    {
        if ($this->type !== null) {
            return $this->type;
        }

        $this->setupFieldsFromProperties();

        foreach ($this->reflected->getMethods() as $reflectedMethod)
        {
            if (isset($reflectedMethod->getAttributes(Field::class)[0])) {
                $this->config['fields'][] = self::setupResolvedField($reflectedMethod);
            }
        }

        $interfaces = [
            get_parent_class($this->reflected->getName()),
                ... class_implements($this->reflected->getName())
        ];

        foreach ($interfaces as $interface)
        {
            if (! is_null($interface = GraphObjectBuilder::getGraphObjectByName($interface))) {
                $this->config['interfaces'][] = $interface->build();
            }
        }

        return $this->type = new ObjectType($this->config);
    }
}