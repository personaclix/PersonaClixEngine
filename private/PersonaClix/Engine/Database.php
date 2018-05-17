<?php

namespace PersonaClix\Engine;

class Database {

	private $PDO = null;

	public function __construct() {
		// Fetch all parameters/arguments passed
		$params = func_get_args();

		// Check if we have at least 5 parameters
		if(count($params) > 5) {
			// Save them to variables for convenience later.
			$host = $params[0];
			$user = $params[1];
			$pass = $params[2];
			$name = $params[3];
			$port = $params[4];

			// Check that host, user, pass, and name are strings and port is an integer.
			if(is_string($host) && is_string($user) && is_string($pass) && is_string($name) && is_int($port)) {
				try {
					// Try to create PDO instance and establish database connection.
					$this->PDO = new \PDO("mysql:host=" . $host . ";dbname=" . $name . ";port=" . $port, $user, $pass);
				} catch (\PDOException $ex) {
					// Catch the PDOException should connection fail to establish.
					error_log("PDOException: " . $ex->getMessage());
				}
			}
		}
	}

	public function select(array $fields, String $table, array $where = []) {
		// Check if a database connection exists by checking for a valid instance of PDO
		// If none, just return.
		if(!$this->PDO instanceof \PDO)
			return;

		// String variable to hold fields for the query.
		$query_fields = "";
		// Integer variable for loop iteration counter.
		$q = 1;
		// Loop through all fields in array.
		foreach ($fields as $field) {
			// Add the current fild to the query string variable.
			$query_fields .= $field;

			// Check if not last iteration and add separator to query variable.
			if($q < count($fields))
				$query_fields .= ", ";

			// Increment iteration counter.
			$q++;
		}

		// WHERE CLAUSE (OPTIONAL)
		// Integer variable for loop iteration counter.
		$w = 1;
		// String variable for the query.
		$where_query = "";
		// Array variable for execution.
		$where_execute = [];
		// Check that a where array was provided.

		// Query variable.
		$query = null;

		if(!empty($where)) {
			// WHERE CLAUSE EXISTS

			// Loop through the where array.
			foreach ($where as $wkey => $wval) {
				// Add current where iteration to the query and execution variables
				$where_query .= $wkey . " = :" . $wkey;
				$where_execute[':' . $wkey] = $wval;
				// Check if not last iteration and add seperator to query variable.
				if($w < count($where))
					$where_query .= " AND ";

				// Increment iteration counter.
				$w++;
			}

			// Prepare the query for execution.
			$query = $this->PDO->prepare("SELECT " . $query_fields . " FROM " . $table . " WHERE " . $where_query);
			// Execute the query with the where execution array.
			$query->execute($where_execute);
		} else {
			// WHERE CLAUSE NOT NEEDED

			// Prepare the query for execution.
			$query = $this->PDO->prepare("SELECT " . $query_fields . " FROM " . $table);
			// Execute the query.
			$query->execute();
		}

		// Check if query returned any results and return them.
		if($query->rowCount() > 0) {
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		}
		// Otherwise return empty array.
		else {
			return [];
		}
	}

}