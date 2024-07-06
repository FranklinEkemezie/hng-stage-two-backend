<?php

namespace App\Core;

/**
 * Response
 * 
 * Models a typical HTTP Response
 */
class Response {
  /** The HTTP sttus code of the response */
  protected int $statusCode;
  /** The headers of the response */
  protected array $headers;
  /** The body of the response */
  protected string $body;

  // Class constants
  public const CONTENT_TYPE_HTML = 'text/html';
  public const CONTENT_TYPE_JSON = 'application/json';

  public const HTTP_OK = 200;
  public const HTTP_NOT_FOUND = 404;
  public const HTTP_INTERNAL_ERROR = 500;
  public const HTTP_BAD_REQUEST = 400;
  public const HTTP_UNAUTHORIZED = 401;
  public const HTTP_FORBIDDEN = 403;
  public const HTTP_CONFLICT = 409;
  public const HTTP_METHOD_NOT_ALLOWED = 405;
  public const HTTP_UNPROCESSABLE_ENTITY = 422;


  public function __construct($statusCode = 200, $body = '', $headers = []) {
    $this -> statusCode = $statusCode;
    $this -> body = $body;
    $this -> headers = $headers;
  }

  /**
   * Sets the status code
   * @param int $statusCode The HTTP status code
   * @return Response Returns the response back
   */
  public function setStatusCode(int $statusCode): Response {
    $this -> statusCode = $statusCode;
    return $this;
  }

  /**
   * Gets the status code
   * @return int Returns the HTTP status code
   */
  public function getStatusCode(): int {
    return $this -> statusCode;
  }

  /**
   * Sets the HTTP header
   * @param string $name The name of the header
   * @param string $value The value of the header
   * @return Response Returns the response back
   */
  public function setHeader($name, $value): Response {
    $this -> headers[$name] = $value;
    return $this;
  }

  /**
   * Gets the value of a specific header.
   * 
   * @param string $name The name of the header.
   * @return string|null The value of the header, or null if the header does not exist.
   */
  public function getHeader($name): string|null {
    return isset($this -> headers[$name]) ? $this -> headers[$name] : null;
  }

  /**
   * Gets all headers of the response.
   * @return array An associative array of headers.
   */
  public function getHeaders(): array {
    return $this -> headers;
  }

  /**
   * Sets the body of the response.
   * @param string $body The body content.
   * @return Response Returns the response object back
   */
  public function setBody($body): Response {
    $this -> body = $body;
    return $this;
  }

  /**
   * Gets the body of the response.
   * @return string The body content.
   */
  public function getBody(): string {
      return $this -> body;
  }

  /**
   * Sends the response to the client.
   * 
   * This method sends the status code, headers, and body to the client.
   * 
   * @return void
   */
  public function send() {
    // Send the status code
    http_response_code($this -> statusCode);

    // Send the headers
    foreach ($this -> headers as $name => $value) {
        header("$name: $value");
    }

    // Send the body
    echo $this -> body;
  }
}