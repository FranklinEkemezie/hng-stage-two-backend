<?php

namespace App\Model;

use App\Core\Database;
use App\Domain\User;
use App\Exception\DbException;
use Exception;

class UserModel extends BaseModel {

  private static function createUserFromRecord(array $record): User {
    return new User(
      $record['user_id'],
      $record['first_name'],
      $record['last_name'],
      $record['email'],
      $record['password'],
      $record['phone']
    );
  }
  public function registerUser(
    string $firstName,
    string $lastName,
    string $email,
    string $password,
    string $phone=""
  ) {
    try {
      return Database::create(
        $this -> conn,
        parent::TABLE_USERS,
        [
          'first_name' => $firstName,
          'last_name' => $lastName,
          'email' => $email,
          'password' => $password,
          'phone' => $phone,
        ],
        "user_id"
      );
    } catch(Exception $e) {
      throw new DbException($e -> getMessage());
    }
  }

  public function getUserByUserId(string $userId): ?User {
    try {
      $userRecord = Database::read(
        $this -> conn, parent::TABLE_USERS, [],
        ['user_id' => $userId]
      );

      if(is_null($userRecord)) return NULL;

      return self::createUserFromRecord($userRecord);
    } catch (DBException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }

  public function getUserByEmail(string $email): ?User {
    try {
      $userRecord = Database::read(
        $this -> conn, parent::TABLE_USERS, [],
        ['email' => $email]
      );

      if(is_null($userRecord)) return NULL;

      return self::createUserFromRecord($userRecord);
    } catch (DBException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }

  /**
   * Whether a user belongs to an organisation
   * @param string $userId The user ID of the user
   * @param string $orgId The organisation ID of the organisation
   * @return bool Returns TRUE if the user is a member of the organisation
   */
  public function userBelongsToOrganisation(string $userId, string $orgId): bool {
    try {
      $userOrgRow = Database::read(
        $this -> conn, parent::TABLE_USER_ORGANISATION, [],
        ['user_id' => $userId, 'org_id' => $orgId]
      );
      
      return !is_null($userOrgRow) ? TRUE : FALSE;
    } catch (DBException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }
}