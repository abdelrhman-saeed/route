<?php

namespace AbdelrhmanSaeed\Route\URI\Constraints;

use AbdelrhmanSaeed\Route\URI\URI;


class URIConstraints implements URIConstraintsInterface
{
    private CONST string OPTIONAL_PARAMETER_REGEX   = '\w+\?';
    private array $constraints = [];

    public function __construct(private URI $uri) {}

    private function wrapSegmentWithDelimiters(string $segment): string {
        return "#\{$segment\}#";
    }

    public function where(string $segment, string $regex): self
    {
        $this->constraints[$this->wrapSegmentWithDelimiters($segment)] = "($regex)";

        return $this;
    }

    public function whereIn(string $segment, array $in): self
    {
        $this->constraints[$this->wrapSegmentWithDelimiters($segment)] = '('. implode("|", $in) . ')';
        
        return $this;
    }

    public function whereOptional(string|array $regex): self
    {
        ! is_array($regex)
            ?: $regex = implode("|", $regex);

        $this->constraints[$this->wrapSegmentWithDelimiters(self::OPTIONAL_PARAMETER_REGEX)] = "*($regex)*";

        return $this;
    }

    public function formatRouteToRegexPattern(): void
    {
        $this->constraints[$this->wrapSegmentWithDelimiters(URIConstraintsInterface::ALPHANUM)]
                = '(' . URIConstraintsInterface::ALPHANUM . ')';

        $this->uri->setRoute(preg_replace(
                    array_keys($this->constraints), $this->constraints, $this->uri->getRoute()));
    }
}