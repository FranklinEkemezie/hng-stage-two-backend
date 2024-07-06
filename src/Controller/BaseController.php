<?php

namespace App\Controller;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Utils\CustomMailer;
use App\Utils\DependencyInjector;
use App\Utils\Logger;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BaseController {

  protected \PDO $conn;
  protected Logger $logger;
  protected Config $config;

  public function __construct(
    DependencyInjector $di,
    protected Request $request
  )
  {
    $this -> conn = $di -> get('db');
    $this -> logger = $di -> get('logger');
    $this -> config = $di -> get('config');
  }


  /**
   * Prepares a response
   * 
   * @param string $body The body
   * @param string $contentType The content type
   * @param string $statusCode The status code
   * @param string $cacheControl Cache control header value
   * @param array $extraHeaders An associative array of extra key-value pairs for the headers
   * 
   * @return Response Returns a Response object
   */
  protected static function prepareResponse(
    string $body,
    string $contentType = Response::CONTENT_TYPE_JSON,
    string $statusCode = Response::HTTP_OK,
    string $cacheControl = NULL,
    array $extraHeaders = []
  ): Response {
    return new Response(
      $statusCode,
      $body,
      array_merge(
        [
          'Content-Type' => $contentType,
          'Cache-Control' => $cacheControl ?: 'no-cache',
          'Access-Control-Allow-Origin' => "*",
          "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS",
          "Access-Control-Allow-Headers" => "Content-Type, Authorization, X-Request-With"
        ],
        $extraHeaders
      )
    );
  }

  /**
   * Prepares the JSON to be sent as Response
   * 
   * @param string $status The status of the request.
   * One of `success`, `error`, `pending`.
   * @param string $message The message
   * @param string $code The code of the status.
   * Preferrably in uppercases and use underscores in place of whitespaces. E.g. VALIDATION_ERROR
   * @param string|array $details The details of the response
   * 
   * @return array
   */
  protected static function prepareResponseJSON(
    string $status,
    string $message,
    string $code,
    string|array $details
  ): array {
    return array(
      'status' => $status,
      'message' => $message,
      'error' => [
        'code' => strtoupper(preg_replace("/\s+/", "_", $code)),
        'details' => $details
      ]
    );
  }


  protected static function getFormData(): array|null {
    return json_decode(file_get_contents('php://input'), TRUE);
  }

  /**
   * Checks if form data is empty
   * 
   * @param array|null $data The form data
   * @return bool Returns TRUE if form data is empty, otherwise FALSE
   */
  protected static function isFormDataEmpty(array|null $data): bool {
    return 
      empty($data) ||
      !is_array($data) ||
      count($data) === 0
    ;
  }
}