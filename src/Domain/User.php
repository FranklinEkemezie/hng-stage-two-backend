<?php

namespace App\Domain;

/**
 * User class
 * 
 * Models a User
 */
class User {
  
  public function __construct(
    private string $userId,
    private string $firstName,
    private string $lastName,
    private string $email,
    private string $password, // should be hashed, for security reasons
    private string $phone
  ) {

  }

  public function getUserId(): string {
    return $this -> userId;
  }

  public function getFirstName(): string {
    return $this -> firstName;
  }

  public function getLastName(): string {
    return $this -> lastName;
  }

  public function getEmail(): string {
    return $this -> email;
  }

  public function getPassword(): string {
    return $this -> password;
  }

  public function getPhone(): string {
    return $this -> phone;
  }

}