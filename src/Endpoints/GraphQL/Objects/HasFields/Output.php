<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\HasFields;

use Attribute;

use GraphQL\Type\Definition\{
    ObjectType, Type, ResolveInfo
};

use AbdelrhmanSaeed\Route\Endpoints\GraphQL\{
    Objects\GraphObjectBuilder, Reflections\ReflectedMethod
};


#[Attribute(Attribute::TARGET_CLASS)]
class Output extends HasFields
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

        return OutputResolver::getResolver($reflectedMethod, $object);
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