<?php

namespace App\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Exception\DbException;
use App\Model\OrganisationModel;
use App\Model\UserModel;
use App\Utils\FormValidator;
use App\Utils\PasswordEncryption;
use Exception;

class UserController extends BaseController {
  /**
   * Registers a user and creates a default organisation
   * Request must be post
   * 
   * @return Response
   */
  public function registerUser(): Response {   
    $errorResponseBody = [
      "status" => "Bad request",
      "message" => "Registration unsuccessful",
      "statusCode" => 401
    ];

    // Request must be post
    if(!$this -> request -> isPost()) {
      return ErrorController::badRequest(json_encode($errorResponseBody));
    }

    $data = self::getFormData();
    if(self::isFormDataEmpty($data)) {
      return ErrorController::badRequest(json_encode($errorResponseBody));
    }

    // Sanitize and filter data
    $data = self::sanitiseAndFilterFormData($data);

    // Validate form data
    $errors = array();

    function getErrorDetails(string $field, string $errorMsg): array {
      return array(
        "field" => $field,
        "message" => $errorMsg
      );
    }

    // Validate first name
    $firstName_ = FormValidator::validateString($data['firstName'], "Firstname");
    if(!$firstName_[0]) $errors[] = getErrorDetails("firstName", $firstName_[1]);

    // Validate last name
    $lastName_ = FormValidator::validateString($data['lastName'], "Lastname");
    if(!$lastName_[0]) $errors[] = getErrorDetails("lastName", $lastName_[1]);

    // Validate email
    $email_ = FormValidator::validateEmail($data["email"]);
    if(!$email_[0]) $errors[] = getErrorDetails("email", $email_[1]);

    // Validate password
    $password_ = FormValidator::validatePassword($data["password"]);
    if(!$password_[0]) $errors[] = getErrorDetails("pasword", $password_[1]);

    // Check if any validation fails
    if(!empty($errors) || count($errors) > 0) {
      return ErrorController::validationError(json_encode(["errors" => $errors]));
    }

    // All goes well, whoops! Continue registration
    try {
      $userModel = new UserModel($this -> conn);
      $organisationModel = new OrganisationModel($this -> conn);

      $this -> conn -> beginTransaction();

      $result = $userModel -> registerUser(
        $data['firstName'],
        $data['lastName'],
        $data['email'],
        // Hash the password here
        PasswordEncryption::hashPassword($data['password']),
        isset($data['phone']) ? $data['phone']  : ""
      );

      $userId = $result[1];
      $orgName = $data['firstName'] . "'s Organisation";

      // Create organisation for the user
      $result_ = $organisationModel -> 
        registerOrganisation($orgName, "", $userId)
      ;

      $this -> conn -> commit();

    } catch(DbException $e) {
      $this -> conn -> rollBack();

      // Check if error is caused by duplicate entry
      if(strpos($e -> getMessage(), '23505') !== FALSE) {
        return ErrorController::badRequest(
          json_encode(
            [
              "status" => "Bad request",
              "message" => "Registration unsuccessful",
              "error" => "User with email alreay exists"
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

      return ErrorController::badRequest(
        json_encode(
          [
            "status" => "Bad request",
            "message" => "Registration unsuccessful"
          ]
        )
      );
    } catch(Exception $e) {
      $this -> conn -> rollBack();

      $this -> logger -> setLogFile(LOGFILE_DIR . 'user.log');
      $this -> logger -> error(
        "An error occurred regigstering user: " .
        $e -> getMessage() . " || Stack trace" .
        $e -> getTraceAsString()
      );

      return ErrorController::badRequest(
        json_encode(
          [
            "status" => "Bad request",
            "message" => "Registration unsuccessful"
          ]
        )
      );
    }

    // Successful registration, go on to log the user in and return response
    $userModel = new UserModel($this -> conn);

    $user = $userModel -> getUserByUserId($userId);

    $jwt = Request::encodeJWTToken(['userId' => $user -> getUserId()]);

    setcookie(Request::USER_LOGGED_IN, $jwt, time() + (86400), "/");

    return self::prepareResponse(
      json_encode([
        "status" => "success",
        "message" => "Registration successful",
        "data" => array(
          "accessToken" => $jwt,
          "user" => [
            "userId" => $user -> getUserId(),
            "firstName" => $user -> getFirstName(),
            "lastName" => $user -> getEmail(),
            "phone" => $user -> getPhone()
          ]
        )
      ])
    );
  }

  public function loginUser(): Response {
    $errorResponse = [
      "status" => "Bad request",
      "message" => "Authentication failed",
      "statusCode" => 401
    ];

    if(!$this -> request -> isPOST()) {
      return ErrorController::unauthorised(
        json_encode($errorResponse)
      );
    }

    $data = self::getFormData();
    if(self::isFormDataEmpty($data)) {
      return ErrorController::unauthorised(
        json_encode($errorResponse)
      );
    }

    // Sanitize and filter data
    $data = self::sanitiseAndFilterFormData($data);

    $email = $data['email'];
    $password = $data['password'];

    $userModel = new UserModel($this -> conn);

    // Try getting the user, if it exists
    $user = $userModel -> getUserByEmail($email);

    if(is_null($user)) // User does not exist
      return ErrorController::unauthorised(
        json_encode($errorResponse)
      )
    ;

    // Check for password match
    $authenticated = PasswordEncryption::verifyPassword(
      $password, $user -> getPassword()
    );

    if(!$authenticated)
      return ErrorController::unauthorised(
        json_encode($errorResponse)
      )
    ;

    // Authentication successful
    $jwt = Request::encodeJWTToken(['userId' => $user -> getUserId()]);

    setcookie(Request::USER_LOGGED_IN, $jwt, time() + (86400), "/");

    return self::prepareResponse(json_encode([
      "status" => "success",
      "message" => "Login successful",
      "data" => array(
        "accessToken" => $jwt,
        "user" => [
          "userId" => $user -> getUserId(),
          "firstName" => $user -> getFirstName(),
          "lastName" => $user -> getLastName(),
          "email" => $user -> getEmail(),
          "phone" => $user -> getPhone()
        ]
      )
    ]));
  }

  /**
   * Returns a (logged in) user records or 
   * the record of users in his/her organisation
   * 
   * @return Response
   */
  public function getUserRecord(string $id): Response {
    $userModel = new UserModel($this -> conn);
    $orgModel = new OrganisationModel($this -> conn);

    $id = sanitiseAndFilterString($id);

    $userLoggedInId = Request::decodeJWTToken($_COOKIE[Request::USER_LOGGED_IN])['userId'];

    $userToRetrieveRecord = $userModel -> getUserByUserId($id);
    
    $userLoggedInOrgs = $orgModel -> getOrganisationsCreatedByUser($userLoggedInId);

    // The logged in user must be the creator of the
    // organisation to access the records of the the user with id : $id

    $userBelongsToOrg = FALSE; // user belongs to any org created by logged in user

    foreach($userLoggedInOrgs as $org) {
      if($userModel -> userBelongsToOrganisation(
        $userToRetrieveRecord -> getUserId(),
        $org -> getOrgId()
      )) { // if the user to retrieve belongs to the organisation created by the logged in user
        $userBelongsToOrg = TRUE;
        break;
      }
    }

    if($userBelongsToOrg || ($userLoggedInId === $id)) {
      $response = array(
        "status" => "success",
        "message" => $userBelongsToOrg 
          ? "Organisation member user record gotten successfully"
          : "User record gotten successfully",
        "data" => [
          "userId" => $userToRetrieveRecord -> getUserId(),
          "firstName" => $userToRetrieveRecord -> getFirstName(),
          "lastName" => $userToRetrieveRecord -> getLastName(),
          "email" => $userToRetrieveRecord -> getEmail(),
          "phone" => $userToRetrieveRecord -> getPhone()
        ]
      );
  
      return self::prepareResponse(json_encode($response));  
    }

    $response = [
      'error' => "User cannot retrieve records"
    ];
    return ErrorController::unauthorised(json_encode($response));
  }


}