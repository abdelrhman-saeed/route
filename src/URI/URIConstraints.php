<?php

namespace AbdelrhmanSaeed\Route\URI;


class URIConstraints
{
    public CONST string ALPHA       = '\w+';
    public CONST string NUM         = '\d+';
    public CONST string ALPHANUM    = '[\w\d]+';
    public CONST string OPTIONAL_PARAMETER_REGEX   = '[\w\d]+\?';


    public function __construct(private URI $uri) {}

    public function where(string $routeParameter, string $regex): self
    {
        $url = $this->uri->getUrl();
        $url = preg_replace("/\{$routeParameter\}/", $regex, $url, -1, $count);

        $count >! 0 ?:
            $this->uri->setUrl($url);

        return $this;
    }

    public function whereIn(string $routeParameter, array $in): self
    {
        return $this->where($routeParameter, '('. implode("|", $in) . ')' );
    }

    public function whereOptional(string|array $regex): self
    {
        ! is_array($regex)
            ?: $regex = implode("|", $regex);

        return $this->where(self::OPTIONAL_PARAMETER_REGEX, "($regex)*");
    }
    public function formatUrlToRegex(): void
    {
        $this->where(self::ALPHANUM, self::ALPHANUM);
        $this->where(self::OPTIONAL_PARAMETER_REGEX, self::OPTIONAL_PARAMETER_REGEX);
    }
}