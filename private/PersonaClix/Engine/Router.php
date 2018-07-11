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
		// FIND ROUTE BASED ON PROVIDED NAME
		// Check if a named route is requested.
		if( $name ) {
			// A named route is requested.
			// Loop through all routes till we find one that matches the name.
			foreach (Router::$routes as $route) {
				// Check if the route has a name registered and that it matches the requested name.
				if( array_key_exists('name', $route['options']) && $route['options']['name'] == $name ) {
					// Return the route info.
					return $route;
				}
			}
		}

		// FIND CURRENT ROUTE BASED ON HOSTNAME AND URI
		// Loop through all routes.
		foreach (Router::$routes as $route) {
			// Match the route to the current URI
			if( $route['route'] == Request::uri() ) {
				// Check if a host option was registered, is a string, and matches the current host.
				if( array_key_exists('host', $route['options']) && is_string( $route['options']['host'] ) && $route['options']['host'] == Request::host() )
					// Return the route info.
					return $route;
				// Check if a host option was registered, is an array, and contains the current host.
				else if( array_key_exists('host', $route['options']) && is_array( $route['options']['host'] ) && in_array( Request::host(), $route['options']['host'] ) )
					// Return the route info.
					return $route;
			}
		}

		// FIND CURRENT ROUTE BASED ON URI
		// Loop through all routes.
		foreach (Router::$routes as $route) {
			// Match route to current URI
			if( $route['route'] == Request::uri() ) {
				return $route;
			}
		}
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

	/**
	 *	Retrieves an option registered with a route or otherwise an empty string.
	 *	Options with arrays as values will return only the first element of that array.
	 *	@param String The option to retrieve.
	 *	@param String (Optional) Name of route to retrieve from. Defaults to current route.
	 *	@return String
	 */
	public static function getRouteOption(String $option, String $name = "") {
		// Get route info.
		$info = Router::getRouteInfo($name);

		// Check if option has been set.
		if( array_key_exists( $option, $info['options'] ) ) {
			// Check if the route option is an array with elements.
			if( is_array( $info['options'][$option] ) && count( $info['options'][$option] ) > 0 ) {
				// Return only the first element of the array.
				return $info['options'][$option][0];
			// Otherwise the route option is probably a string.
			} else {
				// Return the value of the route option.
				return $info['options'][$option];
			}
		}

		// Return empty string as fallback value.
		return "";
	}

	public static function route() {
		// Variable for the requested method
		$request_method = $_SERVER['REQUEST_METHOD'];
		// Variable for the requested route/URI
		$request_route = explode('?', $_SERVER['REQUEST_URI'])[0];
		// Variable for the requested hostname
		$http_host = Request::host();
		
		// MATCH ROUTE AGAINST METHOD, HOST, and URI
		// Loop through all routes.
		foreach (Router::$routes as $route) {
			// Check that the parameters of the current route iteration match the requested method and route.
			if($route['method'] == $request_method && $route['route'] == $request_route) {
				// Check if the route has a specific host registered within its additional options and match against the requested host.
				if( array_key_exists('host', $route['options']) && is_string($route['options']['host']) && $route['options']['host'] == $http_host ) {
					// Return the callable action for that route.
					return $route['action'];
				// Check if the route has an array of specific hosts registered within its additional options and match against the requested host.
				} else if( array_key_exists( 'host', $route['options'] ) && is_array( $route['options']['host'] ) && in_array( $http_host, $route['options']['host'] ) ) {
					// Return the callable action for that route.
					return $route['action'];
				}
			}
		}

		// MATCH ROUTE AGAINST METHOD AND URI
		// Loop through all routes.
		foreach (Router::$routes as $route) {
			// Check that the parameters of the current route iteration match the requested method and route.
			// Since a match against the host failed above, we should ensure there is no host specified with the route.
			if($route['method'] == $request_method && $route['route'] == $request_route && (!isset($route['options']) || !isset($route['options']['host']))) {
				return $route['action'];
			}
		}
	}

}