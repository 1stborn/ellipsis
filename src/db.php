<?php
namespace Ellipsis;

class DB {
	private $db;

	public function __construct(\Corpus\DB $db) {
		$this->db = $db;
	}

	public function __get($name) {
		return property_exists($this, $name) ? $this->{$name} : $this->db->{$name};
	}

	public function scalar($sql, ...$params) {
		if ( sizeof($params) == 1 && is_array($params[0])) {
			$params = $params[0];
		}

		return $this->db->scalar($sql, $params);
	}

	public function query($query, ...$params) {
		if ( sizeof($params) == 1 && is_array($params[0])) {
			$params = $params[0];
		}

		return $this->db->query($query, $params);
	}

	public function __call($name, $arguments) {
		return $this->db->{$name}(...$arguments);
	}
}