<?php

namespace PersonaClix\Engine;

use \PersonaClix\Engine\Helpers\Request;

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
	 *	@return array
	 */
	public static function getRouteInfo(String $name = "") {
		// Loop through all routes
		foreach (Router::$routes as $route) {
			// Check that a name option is specified in the route and that it matches the requested name
			if(array_key_exists("name", $route['options']) && $route['options']['name'] == $name) {
				// Return the route info
				return $route;
			// No name was provided, attempt to find the current route based on its URI.
			} else if(!$name && $route['route'] == Request::uri()) {
				// Route found, return the route info.
				return $route;
			}
		}
		return [];
	}

	/**
	 *	Get route by name. (Only works for routes that have the name option specified)
	 *	Returns the route as a string if found (e.g. /something) or empty string otherwise.
	 *	@return String
	 */
	public static function getRouteURL(String $name) {
		// Get route info
		$route = Router::getRouteInfo($name);

		// Check if route info was returned in the form of an array.
		if(is_array($route)) {
			// Check if a hostname has been specified
			if(array_key_exists("host", $route['options'])) {
				// Check if a string and return the route with the hostname included
				if(is_string($route['options']['host'])) {
					// Do we have an HTTPS connection?
					if(isset($_SERVER['HTTPS']))
						return "https://" . $route['options']['host'] . $route['route'];
					return "http://" . $route['options']['host'] . $route['route'];
				}
				// Check if an array of hostnames and return the route with the first hostname included
				else if(is_array($route['options']['host'])) {
					// Do we have an HTTPS connection?
					if(isset($_SERVER['HTTPS']))
						return "https://" . $route['options']['host'][0] . $route['route'];
					return "http://" . $route['options']['host'][0] . $route['route'];
				}
			}

			// No custom hostname specified.
			// Do we have an HTTPS connection?
			if(isset($_SERVER['HTTPS']))
				// Return an HTTPS URL.
				return "https://" . $_SERVER['HTTP_HOST'] . $route['route'];
			// Return an HTTP URL.
			return "http://" . $_SERVER['HTTP_HOST'] . $route['route'];
		}
		return "";
	}

	public static function route() {
		// Variable for the requested method
		$request_method = $_SERVER['REQUEST_METHOD'];
		// Variable for the requested route/URI
		$request_route = explode('?', $_SERVER['REQUEST_URI'])[0];
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