<?php

namespace AbdelrhmanSaeed\Route\URI\URIActions;


class URIAction {

    public function __construct(private string|array|\Closure $action) {
    }

	/**
	 * @return string|array|\Closure
	 */
	public function getAction(): string|array|\Closure {
		return $this->action;
	}
	
	/**
	 * @param string|array|\Closure $action 
	 * @return self
	 */
	public function setAction(string|array|\Closure $action): self {
		$this->action = $action;
		return $this;
	}

    public function execute(mixed ...$args): void
    {
        ! is_array($this->action)
            ? ($this->action) (...$args)
            : (new $this->action[0])->{$this->action[1]} (...$args);
    }
}