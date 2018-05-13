<?php

namespace PersonaClix\Engine;

class Database {

	private $PDO = null;

	public function __construct(String $host, String $user, String $pass, String $name) {
		try {
			$this->PDO = new \PDO("mysql:host=" . $host . ";dbname=" . $name, $user, $pass);
		} catch (PDOException $ex) {
			error_log("PDOException: " . $ex->getMessage());
		}
	}

	public function select(array $fields, String $table, array $where = []) {
		// Check if a database connection exists by checking for a valid instance of PDO
		// If none, just return.
		if(!$this->PDO instanceof \PDO)
			return;

		$query_fields = "";
		$q = 1;
		// Loop through all fields in array.
		foreach ($fields as $field) {
			$query_fields .= $field;

			if($q < count($fields))
				$query_fields .= ", ";

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
			error_log($query->rowCount() . " results returned by SELECT query.");
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		}
		// Otherwise return empty array.
		else {
			error_log("No results returned by SELECT query.");
			return [];
		}
	}

}