<?php

spl_autoload_register(function ($class_name) {
	if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/../private/' . $class_name . '.php'))
		require_once $_SERVER['DOCUMENT_ROOT'] . '/../private/' . $class_name . '.php';
});

