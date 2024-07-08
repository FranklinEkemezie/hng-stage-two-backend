<?php

namespace App\Utils;

/**
 * Form Validator class
 * 
 * Handles the form validation
 * 
 * @author Ekemezie Franklin <franklynpeter2006@gmail.com>
 */
class FormValidator {

  /**
   * Validate a string
   * 
   * @param string $string The string to validate
   * @param string $name The name to refer to the string
   * @return array [boolean, string|null] Validation result and error message if not valid
   */

   public static function validateString(string|null $string, string $name): array {
    if(empty($string)) {
      return [FALSE, "$name cannot be empty"];
    }

    return [TRUE, NULL];
   }

  /**
   * Validate a username
   * 
   * @param string $username The username to validate
   * @return array [boolean, string|null] Validation result and error message if not valid
   */
  public static function validateUsername(string|null $username): array {
    if(empty($username)) {
      return [FALSE, 'Username cannot be empty'];
    }
    if(strlen($username) < 3 || strlen($username) > 20) { 
      return [FALSE,'Username must be between 3 and 20 characters'];
    }
    if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
      return [FALSE,'Username can only contain letters, numbers, and underscores'];
    }

    return [TRUE, NULL];
  }

  /**
   * Validate an email address
   * 
   * @param string $email The email address to validate
   * @return array [boolean, string|null] Validation result and error message if not valid
   */
  public static function validateEmail(string|null $email): array {
    if(empty($email)) {
      return [FALSE, 'Email cannot be empty.'];
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return [FALSE, 'Invalid email format'];
    }
    
    return [TRUE, NULL];
  }

  /**
   * Validate a password
   * @param string $password The password to validate
   * @return array [boolean, string|null] Validation result and error message if not valid
   */
  public static function validatePassword(string|null $password): array {
    if(empty($password)) {
      return [FALSE, 'Password cannot be empty'];
    }
    // Commented the following lines, not necessary for the HNG TASK
    // if(strlen($password) < 8) {
    //   return [FALSE, 'Password must be at 8 characters long'];
    // }
    // if(!preg_match('/[A-Z]/', $password)) {
    //   return [FALSE, 'Password must contain at least one uppercase letter'];
    // }
    // if(!preg_match('/[a-z]/', $password)) {
    //   return [FALSE, 'Password must contain at least one lowercase letter'];
    // }
    // if(!preg_match('/[0-9]/', $password)) {
    //   return [FALSE, 'Password must contain at least one number'];
    // }
    // if(!preg_match('/[\W]/', $password)) {
    //   return [FALSE, 'Password must contain at least one special character'];
    // }

    return [TRUE, NULL];
  }

  /**
   * Validate a phone number
   *
   * @param string $phoneNumber The phone number to validate
   * @return array [boolean, string|null] Validation result and error message if not valid
   */
  public static function validatePhoneNumber(string|null $phoneNumber): array {
    if(empty($phoneNumber)) {
      return [FALSE, 'Phone number cannot be empty'];
    }
    if(!preg_match('/^\+?[0-9]{10,15}$/', $phoneNumber)) {
      return [FALSE, 'Phone number must be between 10 and 15 digits and may start with a +'];
    }

    return [TRUE, NULL];
  }
}