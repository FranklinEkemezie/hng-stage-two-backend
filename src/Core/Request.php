<?php

namespace App\Core;

use App\Utils\FilteredMap;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Request {
  /** Request protocol - http(s), ftp, mailto, etc. */
  private string $protocol;
  /** Host name */
  private string $host;
  /** Request port */
  private int $port;
  /** Request path */
  private string $path;
  /** Request method */
  private string $method;
  private string $isHTTPS;
  /** GET and POST parameters */
  private FilteredMap $params;
  /** Session variables */
  private FilteredMap $session;

  /* -------------------------
   * Class constants
   * ----------------------- */
  private const GET = 'GET';
  private const POST = 'POST';
  private const PUT = 'PUT';
  private const OPTIONS = 'OPTIONS';
  private const DELETE = 'DELETE';

  public const USER_LOGGED_IN = 'USR_JWT_042';

  public function __construct()
  {
    $this -> protocol = 
      isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on" ?
      "https" : "http"
    ;
    $this -> host = $_SERVER['SERVER_NAME'];
    $this -> port = (int) $_SERVER['SERVER_PORT'];
    $this -> method = strtoupper($_SERVER['REQUEST_METHOD']);
    $this -> path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $this -> params = new FilteredMap(
      array_merge($_POST, $_GET)
    );
    $this -> session = new FilteredMap($_SESSION);
  }

  public static function decodeJWTToken($token, $secret_key): array {
    return (array) JWT::decode(
      $token,
      new Key($secret_key, 'HS256')
    );
  }

  public function encodeJWTToken(array $data, string $secret_key, $expireAfter=NULL): string {
    $issuedAt = new \DateTimeImmutable();
    $expireAt = is_null($expireAfter) ?
      $issuedAt -> modify('+30 days') -> getTimestamp() :
      $issuedAt -> getTimestamp() + $expireAfter
    ;
    
    $data = array_merge(
      array(
        'iat' => $issuedAt -> getTimestamp(),
        'iss' => $this -> getDomainName(),
        'nbf' => $issuedAt -> getTimestamp(),
        'exp' => $expireAt
      ),
      $data
    );

    return JWT::encode($data, $secret_key, 'HS256');
  }

  /**
   * The protocol used to access the resource
   * @return string Returns the protocol used to access
   * the resource, http, https, ftp, mailto etc.
   */
  public function getProtocol(): string {
    return $this -> protocol;
  }

  /**
   * The domain name or IP address of the hosting server
   * @return string Returns the name of the hosting server
   */
  public function getHost(): string {
    return $this -> host;
  }

  /**
   * The port number
   * @return int Returns the port number
   */
  public function getPort(): int {
    return $this -> port;
  }

  /**
   * The path of the request
   * @return string Returns the path of the request
   */
  public function getPath(): string {
    return $this -> path;
  }

  /**
   * The HTTP request method
   * @return string Returns the HTTP request method
   */
  public function getRequestMethod(): string {
    return $this -> method;
  }

  /**
   * Whether request is made with secure HTTP protocol
   * @return bool Returns TRUE if HTTP conncection secure, else FALSE
   */
  public function isHTTPS(): bool {
    return $this -> protocol === 'https';
  }

  /**
   * Whether request method is GET
   * @return bool Returns TRUE if request is GET, otherwise FALSE
   */
  public function isGet(): bool {
    return $this -> method === self::GET;
  }

  /**
   * Whether request is POST
   * @return bool Returns TRUE if request method is POST, otherwise FALSE
   */
  public function isPost(): bool {
    return $this -> method === self::POST;
  }

  /**
   * Specifies whether user is authenticated or not
   * @return bool Returns TRUE if user is authenticated, otherwise FALSE
   */
  public function isAuthenticated(): bool {
    // Check if logged in cook is set.
    return
      isset($_COOKIE[Request::USER_LOGGED_IN]) &&
      !empty($_COOKIE[Request::USER_LOGGED_IN]) &&
      !is_null($_COOKIE[Request::USER_LOGGED_IN])
    ;
  }

  /**
   * Returns the URL of the request
   * @return string Returns the request URL
   */
  public function getUrl(): string {
    return
      $this -> getProtocol() . "://" . 
      $this -> getHost() . ":" .
      $this -> getPort() . 
      $this -> getPath()
    ;
  }

  public function getDomainName(): string {
    return $this -> getHost();
  }

  /**
   * Request parameters.
   * @return FilteredMap Returns the GET and POST query parameters
   * as a `FilteredMap` object
   */
  public function getParams(): FilteredMap {
    return $this -> params;
  }

  /**
   * Get the session variables
   * @return FilteredMap Returns the session variables
   */
  public function getSession(): FilteredMap {
    return $this -> session;
  }

  /**
   * Gets the query parameters of the request URL
   * @return FilteredMap Returns the query parameters
   */
  public function getQueryParameters(): FilteredMap {
    $queryParams = parse_url($this -> getUrl(), PHP_URL_QUERY);
    parse_str($queryParams, $queryParams);

    return new FilteredMap($queryParams);
  }
}