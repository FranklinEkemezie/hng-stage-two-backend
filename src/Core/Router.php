<?php

namespace App\Core;

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
  private static function getRouteRegex(string $route, $info): string {
    // Split the route path
    $route_path_ = explode("/", $route);
    $route_regex_ = array();

    foreach($route_path_ as $path) {
      // Identify parameterized path
      if(isset($path[0]) && $path[0] === ":") {
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
            
          }
        }
      }
    } catch(\Exception $e) {

    } catch(\Error $e) {

    }



  }
}