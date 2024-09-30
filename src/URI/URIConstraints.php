<?php

namespace AbdelrhmanSaeed\Route\URI;


class URIConstraints
{
    public CONST string ALPHA       = '[a-z]+';
    public CONST string NUM         = '[0-9]+';
    public CONST string ALPHANUM    = '\w+';
    private CONST string OPTIONAL_PARAMETER_REGEX   = '\w+\?';

    public function __construct(private URI $uri) {}

    private function replaceRouteSegmentsWithRegex(string $routeParameter, string $regex): self
    {
        $regexedRouteFormat = preg_replace(
                        "#\{$routeParameter\}#", $regex,
                        $this->uri->getRoute(),
                        -1,
                        $count
                        );

        $count >! 0 ?: $this->uri->setRoute($regexedRouteFormat);

        return $this;
    }

    public function where(string $routeParamter, string $regex): self
    {
        return $this->replaceRouteSegmentsWithRegex($routeParamter, "($regex)");
    }

    public function whereIn(string $routeParameter, array $in): self
    {
        return $this->where($routeParameter, '('. implode("|", $in) . ')' );
    }

    public function whereOptional(string|array $regex): self
    {
        ! is_array($regex)
            ?: $regex = implode("|", $regex);

        return $this->replaceRouteSegmentsWithRegex(self::OPTIONAL_PARAMETER_REGEX, "*($regex)*");
    }

    public function formatUrlToRegex(): void
    {
        $this->where(self::ALPHANUM, self::ALPHANUM);
        $this->whereOptional(self::ALPHANUM);
    }
}