<?php

namespace AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints;


interface ConstraintsInterface
{
    public CONST string ALPHA       = '[a-z]+';

    public CONST string NUM         = '[0-9]+';
    
    public CONST string ALPHANUM    = '\w+';

    public function where(string $segment, string $regex): mixed;
    
    public function whereIn(string $segment, array $in): mixed;

    public function whereOptional(array|string $regex): mixed;
}