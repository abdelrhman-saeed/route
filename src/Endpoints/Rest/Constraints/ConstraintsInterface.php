<?php

namespace AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints;


interface ConstraintsInterface
{
    CONST ALPHA       = '[a-z]+';

    CONST NUM         = '[0-9]+';
    
    CONST ALPHANUM    = '\w+';

    public function where(string $segment, string $regex): mixed;
    
    public function whereIn(string $segment, array $in): mixed;

    public function whereOptional(array|string $regex): mixed;
}