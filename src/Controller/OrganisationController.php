<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Exception\DbException;
use App\Model\OrganisationModel;
use App\Utils\FormValidator;
use Exception;

class OrganisationController extends BaseController {

  public function createOrganisation(): Response {
    $errorResponseBody = [
      "status" => "Bad request",
      "message" => "Registration unsuccessful",
      "statusCode" => 401
    ];

    // Request must be by POST
    if(!$this -> request -> isPost()) {
      return ErrorController::badRequest(json_encode($errorResponseBody));
    }

    // Get the logged in user ID, for creating organisation
    $loggedInUserId = Request::decodeJWTToken($_COOKIE[Request::USER_LOGGED_IN])['userId'];

    $data = self::getFormData();
    if(self::isFormDataEmpty($data)) {
      return ErrorController::badRequest(json_encode($errorResponseBody));
    }

    // Sanitise and filter data
    $data = self::sanitiseAndFilterFormData($data);

    // Validate form data
    $errors = array();

    function getErrorDetails(string $field, string $errorMsg): array {
      return array(
        "field" => $field,
        "message" => $errorMsg
      );
    }  

    // Validate organisation name
    $orgName_ = FormValidator::validateString($data['name'], "Organisation name");
    if(!$orgName_[0]) $errors[] = getErrorDetails("name", $orgName_[1]);

    // Check if any validation fails
    if(!empty($errors) || count($errors) > 0) {
      return ErrorController::validationError(json_encode(["errors" => $errors]));
    }

    try {
      $orgModel = new OrganisationModel($this -> conn);

      $result = $orgModel -> registerOrganisation($data['name'], $data['description'], $loggedInUserId);

      $orgId = $result[1];

      $createdOrg = $orgModel -> getOrganisationByOrgId($orgId);
    } catch(DbException $e) {
      $this -> conn -> rollBack();

      $this -> logger -> setLogFile(LOGFILE_DIR . 'db.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(
        json_encode(
          [
            "status" => "Bad request",
            "message" => "Client error",
            "statusCode" => 400
          ]
        )
      );
    } catch(Exception $e) {
      $this -> logger -> setLogFile(LOGFILE_DIR . 'user.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(
        json_encode(
          [
            "status" => "Bad request",
            "message" => "Client error",
            "statusCode" => 400
          ]
        )
      );
    }

    // Successful registration
    $response = array(
      "status" => "Organisation created successfully",
      "data" => [
        "orgId" => $createdOrg -> getOrgId(),
        "name" => $createdOrg -> getName(),
        "description" => $createdOrg -> getDescription()
      ]
    );

    return self::prepareResponse(json_encode($response));

  }

  /**
   * Gets all the organisations the user belongs to or created.
   * User needs to be logged in to perform this action.
   * 
   * @return Response
   */
  public function getUserOrganisations(): Response {
    // If request is POST, call the createOrganisation controller method instead
    if($this -> request -> isPost()) {
      return $this -> createOrganisation();
    }

    $userLoggedInId = Request::decodeJWTToken($_COOKIE[Request::USER_LOGGED_IN])['userId'];

    $orgModel = new OrganisationModel($this -> conn);

    try {
      // Organisation IDS of the organisation user belongs
      $userLoggedInOrgsIds = $orgModel -> getUserOrganisations($userLoggedInId);

      $userLoggedInOrgs = array_map(
        fn($userLoggedInOrgsId) => $orgModel -> getOrganisationByOrgId($userLoggedInOrgsId),
        $userLoggedInOrgsIds
      );

      // User logged in created organisation
      $userLoggedInOrgsCreated = $orgModel -> getOrganisationsCreatedByUser($userLoggedInId);
      $userLoggedInOrgsCreatedDetails = [];
      if(!empty($userLoggedInOrgsCreated)) {
        foreach($userLoggedInOrgsCreated as $org) {
          $userLoggedInOrgsCreatedDetails[] = array(
            "orgId" => $org -> getOrgId(),
            "name" => $org -> getName(),
            "description" => $org -> getDescription()
          );
        }
      }
    } catch(DbException $e) {
      $this -> logger -> setLogFile(LOGFILE_DIR . 'db.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(json_encode(['error' => "Failed to get user's organisations"]));
    } catch(Exception $e) {
      $this -> logger -> setLogFile(LOGFILE_DIR . 'user.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(json_encode(["error" => "Failed to get user's organisations"]));
    }

    $userLoggedInOrgsDetails = array_map(
      fn($org) => [
        "orgId" => $org -> getOrgId(),
        "name" => $org -> getName(),
        "description" => $org -> getDescription()
      ],
      $userLoggedInOrgs
    );

    $response = array(
      "status" => "success",
      "message" => "User organisations gotten successfully",
      "data" => [
        "organisations" => array_merge(
          $userLoggedInOrgsCreatedDetails,
          $userLoggedInOrgsDetails
        )
      ]
    );

    return self::prepareResponse(json_encode($response));
  }

  /**
   * Gets the record for an organisation
   * User must be logged in and can only get the record for 
   * the organisation created by them
   * 
   * @return Response
   */
  public function getOrganisationRecord(string $orgId): Response {
    $userLoggedInId = Request::decodeJWTToken($_COOKIE[Request::USER_LOGGED_IN])['userId'];

    $orgModel = new OrganisationModel($this -> conn);

    try {
      $organisation = $orgModel -> getOrganisationByOrgId($orgId);
    } catch(DbException $e) {
      $this -> logger -> setLogFile(LOGFILE_DIR . 'db.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(json_encode(['error' => "Failed to get organisation's record"]));
    } catch(Exception $e) {
      $this -> logger -> setLogFile(LOGFILE_DIR . 'user.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(json_encode(["error" => "Failed to get organisation's record"]));
    }

    if(is_null($organisation)) {
      return ErrorController::notFound(json_encode(['error' => 'Organisation does not exist']));
    }

    // Gets the organisations created by the user
    $userLoggedInOrgsCreated = $orgModel -> getOrganisationsCreatedByUser($userLoggedInId);

    // Check if the requested organisation is created by the user
    if(!in_array($orgId, array_map(fn($org) => $org -> getOrgId(), $userLoggedInOrgsCreated))) {
      return ErrorController::unauthorised();
    }


    // Return the requestion organisation record
    $response = array(
      "status" => "success",
      "message" => "Organisation record gotten successfully",
      "data" => [
        "orgId" => $organisation -> getOrgId(),
        "name" => $organisation -> getName(),
        "description" => $organisation -> getDescription()
      ]
    );

    return self::prepareResponse(json_encode($response));
  }


  public function  addUser(string $orgId): Response {
    $errorResponseBody = [
      "status" => "Bad request",
      "message" => "Failed to add user to organisation",
      "statusCode" => 401
    ];

    // Request must be by POST
    if(!$this -> request -> isPost()) {
      return ErrorController::badRequest(json_encode($errorResponseBody));
    }

    $userLoggedInId = Request::decodeJWTToken($_COOKIE[Request::USER_LOGGED_IN])['userId'];

    // User must be logged in to add another user
    // The logged in user must be the creator of
    // the organisation before adding any user

    $orgModel = new OrganisationModel($this -> conn);

    $userLoggedInOrgsCreated = $orgModel -> getOrganisationsCreatedByUser($userLoggedInId);

    $userLoggedInIsCreatorOfOrg = in_array(
      $orgId,
      array_map(fn($orgCreated) => $orgCreated -> getOrgId(), $userLoggedInOrgsCreated)
    ); // the logged in user is a creator of the organisation

    if(!$userLoggedInIsCreatorOfOrg) {
      return ErrorController::unauthorised(json_encode($errorResponseBody));
    }

    // Go on to add the user
    $data = self::getFormData();
    if(self::isFormDataEmpty($data)) {
      return ErrorController::badRequest(json_encode($errorResponseBody));
    }

    // Sanitise and filter data
    $data = self::sanitiseAndFilterFormData($data);

    // Validate form data
    $userId_ = FormValidator::validateString($data['userId'], "User ID");

    if(!$userId_[0]) {
      return ErrorController::validationError(json_encode(
        [
          "status" => "Bad Request",
          "message"=> "Client Error",
          "errors" => ["userId" => $userId_[1]]
        ]
      ));
    }

    try {

      $result = $orgModel -> addUserToOrganisation($orgId, $data['userId']);

    } catch(DbException $e) {
      if(strpos($e -> getMessage(), '23505') !== FALSE) {
        return ErrorController::badRequest(
          json_encode(
            [
              "status" => "Bad request",
              "message" => "Failed to add user to organisation",
              "error" => "User is already in the organisation"
            ]
          )
        );  
      }

      $this -> logger -> setLogFile(LOGFILE_DIR . 'db.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(json_encode($errorResponseBody). "Err ocde: " . $e -> getCode());
    } catch(Exception $e) {
      $this -> logger -> setLogFile(LOGFILE_DIR . 'user.log');
      $this -> logger -> error(
        "An error occurred registering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(json_encode($errorResponseBody));
    }

    $response = array(
      "status" => "success",
      "message" => "User added organisation successfully"
    );

    return self::prepareResponse(json_encode($response));

  }

}

