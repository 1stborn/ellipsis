<?php
namespace Ellipsis;

class DB {
	private $db;

	public function __construct(\Corpus\DB $db) {
		$this->db = $db;
	}

	public function getScalar($query, $field = null, array $params = []) {
		$result = $this->db->query($query);
		if ( $result instanceof \mysqli_result && $result->num_rows > 0 ) {
			if ( is_numeric($field) )
				return $result->fetch_row()[$field];
			else if ( !is_null($field) )
				return $result->fetch_assoc()[$field];
			else {
				$row = $result->fetch_row();

				return reset($row);
			}
		}

		return null;
	}

	public function query($query) {
		return $this->query($query);
	}
}