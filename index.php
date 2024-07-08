<?php

################################################
# HELLO!, ALL REQUESTS COME IN HERE
################################################

declare(strict_types=1);

use App\Core\Request;
use App\Core\Router;
use App\Utils\DependencyInjector;
use App\Utils\Logger;
use Dotenv\Dotenv;

// Set default headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/includes/constants.php";
require_once __DIR__ . "/src/includes/functions.php";

session_start();

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv -> load();

// Initialise database connection
$db_conn = $_ENV['DB_CONNECTION'];
$db_host = $_ENV['DB_HOST'];
$db_port = $_ENV['DB_PORT'];
$db_database = $_ENV['DB_DATABASE'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];

$db = new PDO("$db_conn:host=$db_host;port=$db_port;dbname=$db_database;user=$db_username;password=$db_password", $db_username, $db_password);
$db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db -> setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
$db -> setAttribute(PDO::ATTR_STRINGIFY_FETCHES, FALSE);

// Logger
$logger = new Logger('HNG_STAGE_TWO');

// The dependency injector
$di = new DependencyInjector;
$di -> set('db', $db);
$di -> set('logger', $logger);

// Initialise Request
$request = new Request;

// Initialise Router
$router = new Router(ROUTE_MAP_FILENAME, $di);

// Route the request
$response = $router -> route($request);

// Send response
$response -> send();