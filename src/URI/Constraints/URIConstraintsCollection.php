<?php

namespace AbdelrhmanSaeed\Route\URI\Constraints;


class URIConstraintsCollection implements IURIConstraints
{
    
    /**
     * @param array $uriConstraints {
     *  @type UriConstraints
     * }
     */
    public function __construct(private array $uriConstraints)
    {

    }

    public function where(string $segment, string $regex): IUriConstraints
    {
        foreach($this->uriConstraints as $uriConstraints) {
            $uriConstraints->where($segment, $regex);
        }

        return $this;
    }

    public function whereIn(string $segment, array $in): IUriConstraints
    {

        foreach($this->uriConstraints as $uriConstraints) {
            $uriConstraints->whereIn($segment, $in);
        }

        return $this;
    }

    public function whereOptional(array|string $regex): IUriConstraints
    {
        foreach($this->uriConstraints as $uriConstraints) {
            $uriConstraints->whereOptional($regex);
        }

        return $this;
    }

    public function formatRouteToRegexPattern(): void
    {
        foreach($this->uriConstraints as $uriConstraints) {
            $uriConstraints->formatRouteToRegexPattern();
        }
    }
}