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

		// Fix for single hostname registered not completely in lowercase.
		if(!empty($options) && array_key_exists('host', $options) && is_string($options['host'])) {
			// Convert hostname to all lowercase.
			$options['host'] = strtolower($options['host']);
		}
		// Fix for multiple hostnames registered not completely in lowercase.
		if(!empty($options) && array_key_exists('host', $options) && is_array($options['host'])) {
			// Loop through all hostnames
			foreach ($options['host'] as $h => $host) {
				// Convert hostname to all lowercase
				$options['host'][$h] = strtolower($host);
			}
		}

		// Register the route parameters
		Router::$routes[] = [
			'method' => $method,  // The request method GET or POST
			'route' => $route,    // The route e.g. /something
			'action' => $action,  // The callable action
			'options' => $options // Additional optional options
		];
	}

	/**
	 *	Get route info by name. (Only works for routes that have the name option specified)
	 *	Returns an array with route info if match is found, or empty string otherwise.
	 *	@return array|String
	 */
	public static function getInfo(String $name) {
		// Loop through all routes
		foreach (Router::$routes as $route) {
			// Check that a name option is specified and that it matches the requested name
			if(array_key_exists("name", $route['options']) && $route['options']['name'] == $name) {
				// Return the route info
				return $route;
			}
		}
		return "";
	}

	/**
	 *	Get route by name. (Only works for routes that have the name option specified)
	 *	Returns the route as a string if found (e.g. /something) or empty string otherwise.
	 *	@return String
	 */
	public static function getRoute(String $name) {
		// Get route info
		$route = Router::getInfo($name);

		// Check if route info was returned in the form of an array.
		if(is_array($route)) {
			// Return the route.
			return $route['route'];
		}
		return "";
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
				if(!empty($route['options']) && array_key_exists('host', $route['options']) && is_string($route['options']['host']) && $route['options']['host'] == $http_host) {
					// Return the callable action for that route.
					return $route['action'];
				// If not, then check the route has an array of hostnames registered within its additional options
				} else if(!empty($route['options']) && array_key_exists('host', $route['options']) && is_array($route['options']['host'])) {
					// Loop through the hostnames
					foreach ($route['options']['host'] as $host) {
						// Check whether the hostname in the current loop iteration matches the requested hostname
						if($http_host == $host) {
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