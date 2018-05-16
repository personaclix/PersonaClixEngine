<?php

namespace PersonaClix\Engine;

class Router {

	private static $routes = [];

	public static function register(String $method, String $route, callable $action, array $options = []) {
		// Ensure the provided method is in uppercase
		$method = strtoupper($method);
		// Check if provided method is nether GET or POST and change to GET.
		if($method != "GET" && $method != "POST")
			$method = "GET";

		// Register the route parameters
		Router::$routes[] = [
			'method' => $method,  // The request method GET or POST
			'route' => $route,    // The route e.g. /something
			'action' => $action,  // The callable action
			'options' => $options // Additional optional options
		];
	}

	public static function route() {
		// Variable for the requested method
		$request_method = $_SERVER['REQUEST_METHOD'];
		// Variable for the requested route/URI
		$request_route = $_SERVER['REQUEST_URI'];
		// Variable for the requested hostname
		$http_host = $_SERVER['HTTP_HOST'];

		// Loop through all routes
		foreach (Router::$routes as $route) {
			// Check that the parameters of the current route iteration match the requested method and requested route
			if($route['method'] == $request_method && $route['route'] == $request_route) {
				// Check if the route has a specific host registered within its additional options and match against the requested host.
				if(!empty($route['options']) && array_key_exists('host', $route['options']) && is_string($route['options']['host']) && strtolower($route['options']['host']) == $http_host) {
					// Return the callable action for that route.
					return $route['action'];
				// If not, then check the route has an array of hostnames registered within its additional options
				} else if(!empty($route['options']) && array_key_exists('host', $route['options']) && is_array($route['options']['host'])) {
					// Loop through the hostnames
					foreach ($route['options']['host'] as $host) {
						// Check whether the hostname in the current loop iteration matches the requested hostname
						if($http_host == strtolower($host)) {
							// Return the callable action for that route.
							return $route['action'];
						}
					}
				// Otherwise check if either no addional options or no host option was registered.
				} else if(empty($route['options']) || !array_key_exists('host', $route['options'])) {
					// Return the callable action for that route.
					return $route['action'];
				}
			}
		}
	}

}