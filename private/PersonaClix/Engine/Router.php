<?php

namespace PersonaClix\Engine;

class Router {

	private static $routes = [];

	public static function register(String $method, String $route, callable $action) {
		$method = strtoupper($method);
		if($method != "GET" && $method != "POST")
			$method = "GET";

		if(!array_key_exists($method, Router::$routes))
			Router::$routes[$method] = [];
		Router::$routes[$method][$route] = $action;
	}

	public static function route() {
		$method = $_SERVER['REQUEST_METHOD'];
		$route = $_SERVER['REQUEST_URI'];

		if(array_key_exists($method, Router::$routes)) {
			if(array_key_exists($route, Router::$routes[$method])) {
				return Router::$routes[$method][$route];
			}
		}
	}

}