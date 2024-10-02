<?php

namespace AbdelrhmanSaeed\Route\URI\Constraints;


interface IURIConstraints
{
    public CONST string ALPHA       = '[a-z]+';

    public CONST string NUM         = '[0-9]+';
    
    public CONST string ALPHANUM    = '\w+';

    public function where(string $segment, string $regex): self;
    
    public function whereIn(string $segment, array $in): self;

    public function whereOptional(array|string $regex): self;

    public function formatRouteToRegexPattern(): void;
}