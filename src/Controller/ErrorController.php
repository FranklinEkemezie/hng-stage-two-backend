<?php

namespace App\Controller;

use App\Core\Response;

/**
 * Class ErrorController
 * 
 * This class handles different kinds of errors and returns the appropriate response.
 */
class ErrorController extends BaseController {


  private static function prepareErrorResponse(
    int $statusCode,
    ?string $body = NULL
  ): Response {
    return parent::prepareResponse(
      $body,
      Response::CONTENT_TYPE_JSON,
      $statusCode
    );
  }

  /**
   * Handles a 404 Not Found error.
   * 
   * @return Response The response object for a 404 Not Found error.
   */
  public static function notFound(string $body=NULL): Response {
    return self::prepareErrorResponse(
      Response::HTTP_NOT_FOUND,
      $body ?? json_encode(['error' => 'Not Found']),
    );
  }

  /**
   * Handles a 500 Internal Server Error.
   * 
   * @return Response The response object for a 500 Internal Server Error.
   */
  public static function internalServerError(string $body=NULL): Response {
    return self::prepareErrorResponse(
      Response::HTTP_INTERNAL_ERROR,
      $body ?? json_encode(['error' => 'Internal Server Error']),
    );
  }

  /**
   * Handles a 400 Bad Request error.
   * 
   * @return Response The response object for a 400 Bad Request error.
   */
  public static function badRequest(string $body=NULL): Response {
    return self::prepareErrorResponse(
      Response::HTTP_BAD_REQUEST,
      $body ?? json_encode(['error' => 'Bad request']),
    );
  }

  /**
   * Handles a 401 unauthorised error.
   * 
   * @return Response The response object for a 401 unauthorised error.
   */
  public static function unauthorised(string $body=NULL): Response {
    return self::prepareErrorResponse(
      Response::HTTP_UNAUTHORIZED,
      $body ?? json_encode(['error' => 'unauthorised'])
    );
  }

  /**
   * Handles a 403 Forbidden error.
   * 
   * @return Response The response object for a 403 Forbidden error.
   */
  public static function forbidden(string $body=NULL) {
    return self::prepareErrorResponse(
      Response::HTTP_FORBIDDEN,
      $body ?? json_encode(['error' => 'Forbidden'])
    );
  }

  public static function methodNotAllowed(string $body=NULL): Response {
    return self::prepareErrorResponse(
      Response::HTTP_METHOD_NOT_ALLOWED,
      $body ?? json_encode(['error' => 'Method not Allowed']),
    );
  }

  public static function validationError(string $body=NULL): Response {
    return self::prepareErrorResponse(
      Response::HTTP_UNPROCESSABLE_ENTITY,
      $body ?? json_encode(['error' => 'Invalid data'])
    );
  }

  public static function noFormDataSubmitted(string $message, string $code): Response {
    $jsonResponse = self::prepareResponseJSON(
      'error',
      $message,
      $code,
      'No data submitted'
    );

    return ErrorController::badRequest(json_encode($jsonResponse));
  }
}

