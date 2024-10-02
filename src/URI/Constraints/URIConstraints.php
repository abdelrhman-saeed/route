<?php

namespace AbdelrhmanSaeed\Route\URI\Constraints;

use AbdelrhmanSaeed\Route\URI\URI;


class URIConstraints implements IURIConstraints
{
    private CONST string OPTIONAL_PARAMETER_REGEX   = '\w+\?';

    public function __construct(private URI $uri) {}

    private function replaceRouteSegmentsWithRegex(string $segment, string $regex): self
    {
        $regexedRouteFormat = preg_replace(
                        "#\{$segment\}#", $regex,
                        $this->uri->getRoute(),
                        -1,
                        $count
                        );

        $count >! 0 ?: $this->uri->setRoute($regexedRouteFormat);

        return $this;
    }

    public function where(string $segment, string $regex): self
    {
        return $this->replaceRouteSegmentsWithRegex($segment, "($regex)");
    }

    public function whereIn(string $segment, array $in): self
    {
        return $this->where($segment, '('. implode("|", $in) . ')' );
    }

    public function whereOptional(string|array $regex): self
    {
        ! is_array($regex)
            ?: $regex = implode("|", $regex);

        return $this->replaceRouteSegmentsWithRegex(self::OPTIONAL_PARAMETER_REGEX, "*($regex)*");
    }

    public function formatRouteToRegexPattern(): void
    {
        $this->where(self::ALPHANUM, self::ALPHANUM);
        $this->whereOptional(self::ALPHANUM);
    }
}