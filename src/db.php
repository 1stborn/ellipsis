<?php
namespace Ellipsis;

class DB {
	private $db;

	public function __construct(\Corpus\DB $db) {
		$this->db = $db;
	}

	public function scalar($sql, ...$params) {
		return $this->db->scalar($sql, $params);
	}

	public function query($query, ...$params) {
		return $this->db->query($query, $params);
	}
}