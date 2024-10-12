<?php

namespace AbdelrhmanSaeed\Route\URI\Constraints;


trait URIConstraintsTrait
{
    private CONST string OPTIONAL_PARAMETER_REGEX   = '\w+\?';
    private array $constraints = [];

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

        $this->constraints[$this->wrapSegmentWithDelimiters(self::OPTIONAL_PARAMETER_REGEX)] = "?($regex)*";

        return $this;
    }

    private function formatRouteToRegexPattern(string $route): string
    {
        if (! isset($this->constraints[$this->wrapSegmentWithDelimiters(self::OPTIONAL_PARAMETER_REGEX)])) {
            $this->whereOptional(URIConstraintsInterface::ALPHANUM);
        }

        $this->where(URIConstraintsInterface::ALPHANUM, URIConstraintsInterface::ALPHANUM);

        return preg_replace( array_keys($this->constraints), $this->constraints, $route);
    }
}