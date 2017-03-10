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

	private $data = null;

	public final function __construct($key, callable $construct) {
		$this->key       = $key;
		$this->construct = $construct;

		if ( $content = $this->read() ) {
			$this->data = unserialize($content);
		} else {
			$this->data = call_user_func($construct($key));
		}
	}

	public function valid() {
		return !is_null($this->data);
	}

	abstract protected function delete();

	abstract protected function read();

	abstract protected function write($content);

	public function __toString() {
		return call_user_func($this->construct);
	}

	public function flush() {
		if ( $this->data ) {
			$this->write(serialize($this->data));
		} else {
			$this->delete();
		}
	}

	public function __destruct() {
		$this->flush();
	}
}