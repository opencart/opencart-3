<?php
namespace DB;
class PDO {
	private $connection;
	private $data = array();
	private $affected;

	public function __construct($hostname, $username, $password, $database, $port = '3306') {
		if (!$port) {
			$port = '3306';
		}

		try {
			$pdo = @new \PDO('mysql:host=' . $hostname . ';port=' . $port . ';dbname=' . $database, $username, $password, array(\PDO::ATTR_PERSISTENT => false));
		} catch (\PDOException $e) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname . '!');
		}

		if ($pdo) {
			$this->connection = $pdo;
			$this->connection->query("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'");
		}
	}

	public function query($sql, $params = array()) {
		$sql = preg_replace('/(?:\'\:)([a-z0-9]*.)(?:\')/', ':$1', $sql);

		$statement = $this->connection->prepare($sql);

		try {
			if ($statement && $statement->execute($this->data)) {
				$this->data = array();

				if ($statement->columnCount()) {
					$data = $statement->fetchAll(\PDO::FETCH_ASSOC);

					$result = new \stdClass();
					$result->row = isset($data[0]) ? $data[0] : array();
					$result->rows = $data;
					$result->num_rows = count($data);
					$this->affected = 0;

					return $result;
				} else {
					$this->affected = $statement->rowCount();

					return true;
				}

				$statement->closeCursor();
			} else {
				return true;
			}
		} catch (\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' <br/>Error Code : ' . $e->getCode() . ' <br/>' . $sql);
		}

		return false;
	}

	public function escape($value) {
		$key = ':' . count($this->data);

		$this->data[$key] = $value;

		return $key;
	}

	public function countAffected() {
		return $this->affected;
	}

	public function getLastId() {
		return $this->connection->lastInsertId();
	}
	
	public function isConnected() {
		if ($this->connection) {
			return true;
		} else {
			return false;
		}
	}
	
	public function __destruct() {
		unset($this->connection);
	}
}