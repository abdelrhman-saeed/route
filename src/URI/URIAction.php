<?php

namespace AbdelrhmanSaeed\Route\URI;


class URIAction {

    public function __construct(private array|\Closure $action) {
    }

    public function execute(mixed ...$args): void
    {
        ! is_array($this->action)
            ? ($this->action) (...$args)
            : (new $this->action[0])->{$this->action[1]} (...$args);
    }
}