<?php

namespace PersonaClix\Engine\Helpers;

class Form {

	private static $method, $action, $name, $fields = [], $csrf_token = "";

	/**
	 *	Generates and returns the opening HTML Form tag with specified parameters included.
	 *	@param string Method (GET or POST)
	 *	@param string Action (The URL or Path to submit to)
	 *	@param string Name (A name for the form)
	 */
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

	/**
	 *	Generates an Input Field for an HTML Form. Takes an Associative Array of Options.
	 *	Supported Options are: type, name, id, value, class, style, label
	 *	As long as the options are specified in a Key => Value manner, they can be in any order.
	 *	The label option is whether or not a label is required for the field. The name or id option is required for labels to work.
	 *	@param array Optional Array with Options for the Form Field.
	 *	@return string Input Form Field.
	 */
	public static function input(array $options = []) {
		// Check for and retrive the possible options.
		$type = array_key_exists('type', $options) ? $options['type'] : "";
		$name = array_key_exists('name', $options) ? $options['name'] : "";
		$id = array_key_exists('id', $options) ? $options['id'] : "";
		$value = array_key_exists('value', $options) ? $options['value'] : "";
		$class = array_key_exists('class', $options) ? $options['class'] : "";
		$style = array_key_exists('style', $options) ? $options['style'] : "";
		$label = array_key_exists('label', $options) ? $options['label'] : "";

		// Prepare a variable to hold the output.
		$output = "";

		// FIELD LABEL
		// Check if the field has a label specified along with either an ID or a name.
		if($label != '' && ($id != '' || $name != '')) {
			// Output the start of the label tag upto the for attribute.
			$output .= '<label for="';
			// Determine if the label is for the field's ID or Name.
			if($id != '')
				// Its for the ID, output the ID.
				$output .= $id;
			else
				// Its for the Name, output the Name.
				$output .= $name;
			// Output the rest of the opening Label tag (also closing the for attribute).
			$output .= '">';
			// Output the label text.
			$output .= $label;
			// Output the closing Label tag.
			$output .= '</label>';
		}

		// THE FIELD
		// Output the start of the Input field.
		$output .= '<input';
		// Check for and output the Type attribute.
		if($type != '') $output .= ' type="' . $type . '"';
		// Check for and output the Name attribute.
		if($name != '')	$output .= ' name="' . $name . '"';
		// Check for and output the ID attribute.
		if($id != '') $output .= ' id="' . $id . '"';
		// If no ID, check if a label and name have been specified, and output name as ID.
		else if($label != '' && $name != '') $output .= ' id="' . $name . '"';
		// Check for and output the class attribute.
		if($class != '') $output .= ' class="' . $class . '"';
		// Check for and output the style attribute.
		if($style != '') $output .= ' style="' . $style . '"';
		// Check for and output the value attribute.
		if($value != '') $output .= ' value="' . $value . '"';
		// Output the end of the input tag.
		$output .= ">\n";

		//return ($label != '' && ($id != '' || $name != '') ? '<label for="' . ($id != '' ? $id : $name) . '">' . $label . '</label>' : '') . '<input' . ($type != "" ? ' type="' . $type . '"' : '') . ($name != "" ? ' name="' . $name . '"' . ($id != "" ? ' id="' . $id . '"' : ($label != '' ? ' id="' . $name . '"' : '')) : ($id != '' ? ' id="' . $id . '"' : '')) . ($value != "" ? ' name="' . $name . '"' : '') . ">\n";

		// Return everything to output.
		return $output;
	}

	/**
	 *	Generate a hidden field with the CSRF Token. Will also generate a token if one doesn't exist.
	 *	CSRF Tokens are saved in Cookies that last for 5 minutes.
	 *	@param bool Whether to return only the token itself.
	 *	@return string Either just the CSRF Token or an HTML Hidden Form Field with the CSRF Token.
	 */
	public static function csrf_token(bool $tokenOnly = false) {
		// Check if the token cookie has not been set yet
		if(!isset($_COOKIE['csrf_token'])) {
			// Generate a token based on a SHA1 Hash of the current date and time
			$token = sha1(date('YmdHis'));
			// Set the token cookie with the generated token to expire in 5 minutes.
			setcookie("csrf_token", $token, time() + (60 * 5));
		// Token has already been set.
		} else {
			// Retrieve the token from the cookie.
			$token = $_COOKIE['csrf_token'];
		}

		// Is just the token being requested?
		if ($tokenOnly)
			// Return just the token.
			return $token;
		
		// Otherwise generate the hidden field to hold the token

		// Field options
		$field_options = [
			'type' => 'hidden',
			'name' => 'csrf_token',
			'value' => $token
		];

		// Generate and return the field.
		return Form::input( $field_options );
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