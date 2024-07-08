<?php

namespace App\Model;

use App\Core\Database;
use App\Domain\Organisation;
use App\Exception\DbException;
use Exception;

class OrganisationModel extends BaseModel {

  private static function createOrganisationFromRecord(array $record): Organisation {
    return new Organisation(
      $record['org_id'],
      $record['name'],
      $record['description'],
      $record['created_by']
    );
  }

  public function registerOrganisation(
    string $name,
    string $description="",
    string $createdById
  ): array {
    try {
      return Database::create(
        $this -> conn,
        parent::TABLE_ORGANISATIONS,
        [
          'name' => $name,
          'description' => $description,
          'created_by' => $createdById
        ],
        "org_id"
      );
    } catch (DBException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }

  /**
   * Adds user to an organisation
   * @param string $orgId The organisation ID 
   * @param string $userId The user ID
   * @return array 
   */
  public function addUserToOrganisation(string $orgId, string $userId): array {
    try {
      return Database::create(
        $this -> conn,
        parent::TABLE_USER_ORGANISATION,
        [
          'user_id' => $userId,
          'org_id' => $orgId
        ],
        "user_id"
      );
    } catch (DBException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }

  public function getOrganisationByOrgId(string $orgId): ?Organisation {
    try {
      $orgRecord = Database::read(
        $this -> conn,
        parent::TABLE_ORGANISATIONS,
        [],
        ['org_id' => $orgId]
      ); 
      
      if(is_null($orgRecord)) return NULL;

      return self::createOrganisationFromRecord($orgRecord);
    } catch (DbException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }

  /**
   * Get the organisation created by the user
   * @param string $creatorId The user ID of the user that created the organisation
   * @return Organisation[] Returns an array of organisation created by the user
   */
  public function getOrganisationsCreatedByUser(string $creatorId): array {
    try {
      $orgsCreatedRecord = Database::read(
        $this -> conn,
        parent::TABLE_ORGANISATIONS,
        [],
        ['created_by' => $creatorId],
        FALSE,
        TRUE
      ); 
      
      if(is_null($orgsCreatedRecord)) return [];

      return array_map(
        fn($orgRecord) => self::createOrganisationFromRecord($orgRecord),
        $orgsCreatedRecord
      );
    } catch (DbException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }

  /**
   * Gets the user ID of the members of the organisation
   * @param string $orgId The organisation ID
   * @return array The members of the organisation. Creator is not included
   */
  public function getOrganisationMembers(string $orgId): array {
    try {
      $orgMembersRows = Database::read(
        $this -> conn, parent::TABLE_USER_ORGANISATION, ['user_id'],
        ['org_id' => $orgId], FALSE, TRUE
      );

      if(is_null($orgMembersRows)) return [];

      return array_map(fn($orgMembersRow) => $orgMembersRow['user_id'], $orgMembersRows);
    } catch (DbException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }

  /**
   * Gets the organisation that a user belongs to
   */
  public function getUserOrganisations(string $userId): array {
    try {
      $userOrgIdRows = Database::read(
        $this -> conn, parent::TABLE_USER_ORGANISATION, ['org_id'],
        ['user_id' => $userId], FALSE, TRUE
      ) ?? [];

      return array_map(fn($userOrgIdRow) => $userOrgIdRow['org_id'], $userOrgIdRows);
    } catch (DbException $e)  {
      throw new DBException($e -> getMessage());
    } catch (Exception $e) {
      throw new Exception($e -> getMessage());
    }
  }
}