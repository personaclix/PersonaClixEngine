<?php

namespace PersonaClix\Engine;

class Configuration {

	private $path;
	private $config = [];

	public function __construct(String $path) {
		$this->path = $path;
		if(file_exists($path))
			$this->config = json_decode(file_get_contents($path), true);

		if($this->config == NULL) {
			$this->config = json_last_error_msg();
		}
	}

	public function get(String $key = "") {
		if($key && is_array($this->config) && array_key_exists($key, $this->config)) {
			return $this->config[$key];
		}
		return $this->config;
	}

}