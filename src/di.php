<?php
namespace Ellipsis;

use Interop\Container\ContainerInterface;

abstract class Di {
	/**
	 * @var ContainerInterface
	 */
	protected $ci;

	public function __construct(ContainerInterface $ci) {
		$this->ci = $ci;
	}

	public function __get($name) {
		return $this->ci->get($name);
	}
}