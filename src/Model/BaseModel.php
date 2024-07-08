<?php

namespace App\Model;

abstract class BaseModel {

  protected const TABLE_USERS = 'users';
  protected const TABLE_ORGANISATIONS = 'organisations';
  protected const TABLE_USER_ORGANISATION = 'user_organisation';


  public function __construct(protected \PDO $conn) {
    
  }

  /**
   * Get the last insert ID for MySQL database
   */
  public function getLastInsertIdMySQL() {
    return $this  -> conn -> lastInsertId();
  }

  /**
   * Get the last insert ID for Postgre SQL
   */
  public function getLastInsertIdPgSQL() {
    return $this -> conn -> query("SELECT lastval()");
  }
}