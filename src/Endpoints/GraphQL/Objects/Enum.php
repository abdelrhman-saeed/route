<?php

namespace AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects;

use Attribute;
use GraphQL\Type\Definition\EnumType;
use AbdelrhmanSaeed\Route\Endpoints\GraphQL\Objects\{GraphObject, GraphObjectBuilder};


#[Attribute(Attribute::TARGET_CLASS)]
class Enum extends GraphObject {

    public function build(): EnumType
    {
        if (! is_null($this->type)) {
            return $this->type;
        }

        $docBlock = GraphObjectBuilder::getDocBlockFactoryInterface();
        $enumDocBlock = $docBlock->create($this->reflected->getDocComment());

        $config = [
            'name'          => $this->reflected->getShortName(),
            'description'   => $enumDocBlock->getDescription()->__tostring(),
            'values'        => []
        ];

        $enumFullName   = $this->reflected->getName();
        $cases          = $enumFullName::cases();

        foreach ($cases as $case)
        {
            $caseDescription = $this->reflected->getCase($case->name);
            $caseDescription = $docBlock->create($caseDescription->getDocComment())
                                        ->getDescription()
                                        ->__tostring();

            $config['values'][$case->name] = [
                'value'         => $case->value,
                'description'   => $caseDescription
            ];
        }

        return $this->type = new EnumType($config);
    }
}
