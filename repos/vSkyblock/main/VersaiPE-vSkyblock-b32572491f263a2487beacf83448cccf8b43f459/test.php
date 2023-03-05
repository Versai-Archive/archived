<?php

require "vendor/autoload.php";

use Medoo\Medoo;

function connect()
{
	$db = new Medoo([
		'type' => 'mysql',
		'host' => 'localhost',
		'database' => 'oqex',
		'username' => 'root',
		'password' => 'root'
	]);

	return $db->info();
}

var_dump(connect());