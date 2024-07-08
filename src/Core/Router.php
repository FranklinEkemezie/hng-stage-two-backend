<?php

namespace App\Core;

use App\Controller\BaseController;
use App\Controller\ErrorController;
use App\Exception\NotFoundException;
use App\Utils\DependencyInjector;

/**
 * Router class
 * 
 * Handles HTTP request and routes them to the
 * appropriate controller and executes the method
 */

class Router {
  private array $routeMap; // list of defined routes

  // Class Constants
  public const CONTROLLER_NAMESPACE = "\\App\\Controller";
  public const REGEX_PATTERNS = array(
    "number" => "\d+",
    "string" => "[a-zA-Z0-9-_]+",
  );
  

  public function __construct(
    string $routeMapFile,
    private DependencyInjector $di
  ) {
    if(!file_exists($routeMapFile)) {
      throw new NotFoundException("Route map file `$routeMapFile` not found");
    }

    $this -> routeMap = json_decode(
      file_get_contents($routeMapFile),
      TRUE
    );
  }

  /**
   * Gets the corresponding regex path to match a path to the route using the route info
   * @param string $route The route to be used to generate the regex
   * @param array $info An associative array of information about the route. This information is
   * used to generate the regex pattern for the route
   * @return string The regex pattern for the route 
   */
  private static function getRouteRegex(string $route, array $info): string {
    // Split the route path
    $route_path_ = explode("/", $route);
    $route_regex_ = array();

    foreach($route_path_ as $path) {
      // Identify parameterized path
      if(isset($path[0]) && !empty($path[0]) && $path[0] === ":") {
        $param = substr($path, 1);
        $data_type = $info["params"][$param];
        $data_type_regex = self::REGEX_PATTERNS[$data_type];
        
        // Replace the parameterized path with the corresponding regular expression
        $path = $data_type_regex; 
      }

      $route_regex_[] = $path;
    }

    $route_regex = implode("\/", $route_regex_);
    
    return "/^$route_regex$/";
  }

  /**
   * Gets the controller responsible for an action(method)
   * 
   */
  private function getActionController($route, $request): BaseController {
    $controllerName = $this -> routeMap[$route]['controller'] . "Controller";
    $controllerName = self::CONTROLLER_NAMESPACE . "\\" . $controllerName;
    $controller = new $controllerName($this -> di, $request);

    return $controller;
  }

  /**
   * Extract the parameters from the request path
   * 
   * @param string $route The route for matching and extracting the parameters
   * @param string $path The request to extract the parameters from
   * 
   * @return array An associative array of the extracted parameters with the key as the variable name of the parameter
   * and the corresponding value as the value of the parameter.
   */
  private static function extractParams(string $route, string $path): array {
    $params = [];

    $route_parts = explode("/", $route);
    $path_parts = explode("/", $path);

    foreach($route_parts as $index => $route_part) {
      if(isset($route_part[0]) && $route_part[0] === ":") {
        $params[substr($route_part, 1)] = $path_parts[$index];
      }
    }

    return $params;
  }

  /**
   * Executes the controller
   * 
   * @param string $route The route
   * @param Request $request The request
   * 
   * @return Response The response
   */
  private function executeController($route, $request): Response {
    $controller = self::getActionController($route, $request);
    $controllerMethod = $this -> routeMap[$route]['action'];

    $path = substr($request -> getPath(), 1);
    $params = self::extractParams($route, $path);

    return call_user_func(
      [$controller, $controllerMethod],
      ...$params
    );
  }


  /**
   * Routes request to the appropriate controller method and executes it
   * @param Request $request The request to route
   * @return Response Returns the response from the controller
   */
  public function route(Request $request): Response {
    // Check if request is OPTIONS: handle preflight
    if($request -> getRequestMethod() === Request::OPTIONS) {
      // Send an empty response and return 204
      http_response_code(204);
      exit();
    }

    // Set logger file
    $logger = $this -> di -> get('logger');

    try {
      $pathRoute = substr($request -> getPath(), 1);

      foreach($this -> routeMap as $route => $routeInfo) {
        if(preg_match(self::getRouteRegex($route, $routeInfo), $pathRoute)) {
          // Check if authentication is required
          $authenticationRequired = isset($routeInfo['authentication']) && $routeInfo['authentication'];
          if($authenticationRequired && !$request -> isAuthenticated()) {
            return ErrorController::unauthorised(json_encode(
              ['error' => 'User is not logged in']
            ));
          }

          // Execute the controller method
          return $this -> executeController($route, $request);
        }
      }

      return ErrorController::notFound();
    } catch(\Exception $e) {
      // Log error to log file
      $logger -> setLogFile(LOGFILE_DIR . 'default.log');
      $logger -> error(
        "Error occurred: " . 
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::internalServerError();
    } catch(\Error $e) {
      // Log error to log file
      $logger -> setLogFile(LOGFILE_DIR . 'default.log');
      $logger -> error(
        "Error occurred: " . 
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::internalServerError();
    }



  }
}