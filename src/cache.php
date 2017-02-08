<?php
namespace Ellipsis;

abstract class Cache {
	/**
	 * @var string
	 */
	private $key;
	/**
	 * @var callable
	 */
	private $construct;

	public function __construct($key, $construct) {
		$this->key       = $key;
		$this->construct = $construct;
	}

	abstract protected function read();

	abstract protected function write($content);

	public function __toString() {
		return call_user_func($this->construct);
	}
}