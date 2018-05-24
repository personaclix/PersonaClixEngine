<?php

namespace PersonaClix\Engine\Helpers;

class Form {

	private static $method, $action, $name, $fields = [], $csrf_token = "";

	public static function open(String $method = "", String $action = "", String $name = "") {
		// Ensure method is in uppercase.
		$method = strtoupper($method);

		// Check that method is either GET or POST and default to GET.
		if($method != "" && $method != "POST")
			$method = "GET";

		// Save the parameters for later.
		Form::$method = $method;
		Form::$action = $action;
		Form::$name = $name;

		return '<form' . ($method != "" ? ' method="' . $method . '"' : '') . ($action != "" ? ' action="' . $action . '"' : '') . ($name != "" ? ' name="' . $name . '"' : '') . ">\n";
	}

	public static function input(String $type, String $name = "", String $value = "") {
		Form::$fields[] = [
			'type' => $type,
			'name' => $name,
			'value' => $value
		];

		return '<input type="' . $type . '" name="' . $name . '"' . ($value != "" ? ' name="' . $name . '"' : '') . ">\n";
	}

	public static function csrf_token(bool $tokenOnly = false) {
		if(!isset($_COOKIE['csrf_token'])) {
			$token = sha1(date('YmdHis'));
			setcookie("csrf_token", $token, time() + (60 * 5));
		} else {
			$token = $_COOKIE['csrf_token'];
		}
		
		Form::$fields[] = [
			'type' => 'hidden',
			'name' => 'csrf_token',
			'value' => $token
		];

		Form::$csrf_token = $token;

		if ($tokenOnly)
			return $token;
		return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . $token . "\">\n";
	}

	public static function close() {
		return "</form>\n";
	}

	public static function field(String $name) {
		foreach (Form::$fields as $field) {
			if($field['name'] == $name && isset( $_{Form::$method}[$name] )) {
				return $_{Form::$method}[$name];
			}
		}

		return "";
	}

}