<?php

namespace PersonaClix\Engine;

class Database {

	private $PDO = null;

	public function __construct() {
		// Fetch all parameters/arguments passed
		$params = func_get_args();

		// Check if we have at least 4 parameters
		if(count($params) >= 4) {
			// Save them to variables for convenience later.
			$host = $params[0];
			$user = $params[1];
			$pass = $params[2];
			$name = $params[3];

			// Check for optional 4th parameter to use as port number, and default to 3306 if none.
			if(isset($params[4]))
				$port = $params[4];
			else
				$port = 3306;

			// Check that host, user, pass, and name are strings and port is either a string or an integer.
			if(is_string($host) && is_string($user) && is_string($pass) && is_string($name) && (is_int($port) || is_string($port))) {
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

	/**
	 *	Select records from the database.
	 *	@param array Fields/Columns to Select
	 *	@param string Name of the Table to select from
	 *	@param array (Optional) Where clause to narrow results in the form of Field => Value array.
	 */
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

	/**
	 *	Insert a record into the database.
	 *	@param String Table Name
	 *	@param array Associative Array of Key => Value pairs for the fields and data to insert.
	 *	@return boolean Whether query executed successfully or not.
	 */
	public function insert(String $table, array $fields) {
		// Check if a database connection exists by checking for a valid instance of PDO
		// If none, just return.
		if(!$this->PDO instanceof \PDO)
			return;

		// String variable to hold field names for the query.
		$query_fields = "";
		// String variable to hold field value tags for the query.
		$query_field_tags = "";
		// String variable to hold the execution array of :field => value pairs.
		$query_execute = [];
		// Integer variable for loop iteration counter.
		$q = 1;
		// Loop through all fields in array.
		foreach ($fields as $field_name => $field_value) {
			// Add the current field to the query fields variable.
			$query_fields .= $field_name;
			// Add the current field to the query field tags variable.
			$query_field_tags .= ":" . $field_name;
			// Add the current field to the query execution array.
			$query_execute[':' . $field_name] = $field_value;

			// Check if not last iteration and add separator to variables.
			if($q < count($fields)) {
				$query_fields .= ", ";
				$query_field_tags .= ", ";
			}

			// Increment iteration counter.
			$q++;
		}

		// Prepare the insert query for execution.
		$query = $this->PDO->prepare("INSERT INTO " . $table . "(" . $query_fields . ") VALUES(" . $query_field_tags . ")");
		// Execute the insert query.
		$query_execution = $query->execute($query_execute);
		// Return whether the query executed successfully as a boolean.
		return $query_execution;
	}

}