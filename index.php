<?php

################################################
# HELLO!, ALL REQUESTS COME IN HERE
################################################

declare(strict_types=1);

use App\Core\Request;
use App\Utils\DependencyInjector;

// Set default headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


require_once __DIR__ . "/vendor/autoload.php";

session_start();

// Initialise database connection
$db_conn = $_ENV['DB_CONNECTION'];
$db_host = $_ENV['DB_HOST'];
$db_port = $_ENV['DB_PORT'];
$db_database = $_ENV['DB_DATABASE'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];

$db = new PDO("$db_conn:host=$db_host;dbname=$db_name", $db_username, $db_password);
$db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db -> setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
$db -> setAttribute(PDO::ATTR_STRINGIFY_FETCHES, FALSE);


// The dependency injector
$di = new DependencyInjector;
$di -> set('db', $db);

// Initialise Request
$request = new Request;

// Initialise Router
$routeMapFilename = __DIR__ . "/config/routes.json";
$router;


echo "Hello, Mark! This is HNG";