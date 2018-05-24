<?php

namespace PersonaClix\Engine\Helpers;

class Request {

	public static function https() { return isset( $_SERVER['HTTPS'] ); }
	public static function host() { return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ""; }
	public static function uri() { return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ""; }
	public static function get(String $key) { return isset($_GET[$key]) ? $_GET[$key] : ""; }
	public static function post(String $key) { return isset($_POST[$key]) ? $_POST[$key] : ""; }

}