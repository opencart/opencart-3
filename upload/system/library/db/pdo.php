<?php
namespace DB;
/**
 * Class PDO
 *
 * @package System\Library\DB
 */
class PDO {
	/**
	 * @var \PDO|null
	 */
	private ?\PDO $connection;
	/**
	 * @var array<string, string>
	 */
	private array $data = [];
	/**
	 * @var int
	 */
	private int $affected;

	/**
	 * Constructor
	 *
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param string $port
	 */
	public function __construct(string $hostname, string $username, string $password, string $database, string $port = '') {
		if (!$port) {
			$port = '3306';
		}

		try {
			$pdo = new \PDO('mysql:host=' . $hostname . ';port=' . $port . ';dbname=' . $database . ';charset=utf8mb4', $username, $password, [\PDO::ATTR_PERSISTENT => false, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci']);
		} catch (\PDOException $e) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname . '!');
		}

		$this->connection = $pdo;

		$this->query("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
		$this->query("SET FOREIGN_KEY_CHECKS = 0");

		// Sync PHP and DB time zones
		$this->query("SET `time_zone` = '" . $this->escape(date('P')) . "'");
	}

	/**
	 * Query
	 *
	 * @param string $sql
	 *
	 * @return \stdClass|true
	 */
	public function query(string $sql) {
		$sql = preg_replace('/(?:\'\:)([a-z0-9]*.)(?:\')/', ':$1', $sql);

		$statement = $this->connection->prepare($sql);

		try {
			if ($statement && $statement->execute($this->data)) {
				$this->data = [];

				if ($statement->columnCount()) {
					$data = $statement->fetchAll(\PDO::FETCH_ASSOC);
					$statement->closeCursor();

					$result = new \stdClass();
					$result->row = $data[0] ?? [];
					$result->rows = $data;
					$result->num_rows = count($data);
					$this->affected = 0;

					return $result;
				} else {
					$this->affected = $statement->rowCount();
					$statement->closeCursor();

					return true;
				}
			} else {
				return true;
			}
		} catch (\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' <br/>Error Code : ' . $e->getCode() . ' <br/>' . $sql);
		}
	}

	/**
	 * Escape
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function escape(string $value): string {
		$key = ':' . count($this->data);

		$this->data[$key] = $value;

		return $key;
	}

	/**
	 * countAffected
	 *
	 * @return int
	 */
	public function countAffected(): int {
		return $this->affected;
	}

	/**
	 * getLastId
	 *
	 * @return ?int
	 */
	public function getLastId(): ?int {
		$id = $this->connection->lastInsertId();

		return $id ? (int)$id : null;
	}

	/**
	 * isConnected
	 *
	 * @return bool
	 */
	public function isConnected(): bool {
		return $this->connection !== null;
	}

	/**
	 * Destructor
	 *
	 * Closes the DB connection when this object is destroyed.
	 */
	public function __destruct() {
		$this->connection = null;
	}
}
