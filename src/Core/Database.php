<?php

namespace App\Core;

use \PDO;
use \Exception;
use App\Exception\DBException;


class Database {
  /**
   * Creates(Inserts) a row with the given data
   * 
   * @param PDO $conn Specifies the database connection to use
   * @param string $table Specifies the table to insert the row
   * @param array $data The data to be inserted
   * 
   * @return array [boolean, int|null] Returns an array with two elements.
   * The first element specifies whether the operation is successful, and
   * the second element returns the unique ID if available
   */
  public static function create(PDO $conn, string $table, array $data, string $returning=NULL): array {
    $columns = join(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $query = "INSERT INTO $table ($columns) VALUES ($placeholders)" . (!is_null($returning) ? " RETURNING $returning" : "");

    try {
      $stmt = $conn -> prepare($query);
      $res = $stmt -> execute($data);

      return [
        $res,
        !is_null($returning) ? $stmt -> fetchColumn() : NULL
      ];

    } catch (Exception $e) {
      throw new DBException($e -> getMessage());
    }

  }

    /**
   * Selects row(s) from database table
   * 
   * @param PDO $conn Specifies the database connection to use
   * @param string $table The table to select data from
   * @param array $fields An array of field names to retrieve. An empty array will retrieve all the fields/columns
   * @param ?array $condtions An associative array specifying the conditions for
   * a row to be selected. The key-value element of the array demands that
   * the row to be selected must have the field `key` with the corresponding value.
   * An empty array or NULL value selects all the records in the table.
   * @param bool $select_any Specifies whether to select a row which satisfies
   * any of the condition or that which satisfies all the conditions.
   * @param bool $select_all Specifies whether to return all the selected rows or just the first one.
   * Default is FALSE, that is the first row is returned by default
   * 
   * @return array|NULL Returns the selected row. If more than one records is to be selected,
   * an indexed array holding each record as an associative array is returned, otherwise an associative
   * array of the first record is returned.
   */
  public static function read(
    PDO $conn,
    string $table,
    array $fields,
    ?array $conditions=NULL,
    bool $selectAny=FALSE,
    bool $selectAll=FALSE
  ): array|NULL {
    // Fields array must not be empty: if empty, select all
    $fields_ = count($fields) === 0 || empty($fields) ? "*" : join(", ", $fields);

    // COnditions
    $boolOperator = $selectAny ? 'OR' : 'AND';
    $conditions = $conditions ?? [];
    $conditions_ = join(" $boolOperator ", array_map(
      fn($value) => "$value = :$value", // parameterize the columns
      array_keys($conditions)
    ));

    $query = !empty(trim($conditions_)) ?
      "SELECT $fields_ FROM $table WHERE $conditions_" :
      "SELECT $fields_ FROM $table"
    ;

    try {
      $stmt = $conn -> prepare($query);
      $stmt -> execute($conditions);

      $row = $stmt -> fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
      throw new DBException("SQL query: $query" . $e -> getMessage());
    }

    if(count($row) === 0) return NULL; // return NULL if no record was selected

    return $selectAll ? $row : $row[0];
  }

  /**
   * Update row(s) from a database table 
   * 
   * @param PDO $conn The database connection
   * @param string $table The table to update
   * @param array $data The data used to update the rows
   * @param array $conditions The conditions to update a row.
   * Default is NULL which updates all rows in a table
   * @param bool $selectAny Specifies whether to update any row which
   * satisfies any of the given conditios. Default is FALSE specifies that
   * a row must satisfy all conditions before it is updated
   * @param bool $updateAll 
   * 
   * @return bool Returns TRUE if the operation is successful, otherwise FALSE
   */
  public static function update(
    PDO $conn,
    string $table,
    array $data,
    array $conditions=NULL,
    bool $updateAny=FALSE
  ): bool {
    $paramValues = join(", ", array_map(
      fn($value) => "$value = :$value", // parameterize values here
      array_keys($data)
    )); // parameterized values

    // Conditions
    $boolOperator = $updateAny ? 'OR' : 'AND';
    $conditions = $conditions ?? [];
    $conditions_ = join(" $boolOperator ", array_map(
      fn($value) => "$value = :$value", // parameterize values here
      array_keys($conditions)
    ));

    $query = !empty(trim($conditions_))  ?
      "UPDATE $table SET $paramValues FROM WHERE $conditions_" :
      "UPDATE $table SET $paramValues"
    ;

    try {
      $stmt = $conn -> prepare($query);
      return $stmt -> execute($conditions);
    } catch (Exception $e) {
      throw new DBException("SQL query: $query" . $e -> getMessage());
    }
  }

  /**
   * Delete a record
   * 
   * @param PDO $conn The database connection
   * @param string $table The database table
   * @param array $conditions The conditions to delete a row.
   * Default is NULL, which deletes all the rows
   * @param bool $deleteAny Specifies whether to delete a row which
   * satisfies any of the conditions or all of the conditions.
   * Default is FALSE, which specifies that for a row to be deleted, it
   * must satisfy all of the conditions
   * 
   */
  public static function delete(
    PDO $conn,
    string $table,
    array $conditions=NULL,
    bool $deleteAny=FALSE
  ): bool {
    // Conditions
    $boolOperator = $deleteAny ? 'OR'  : 'AND';
    $conditions = $conditions ?? [];
    $conditions_ = join(" $boolOperator ", array_map(
      fn($value) => "$value = :$value", // parameterise values here
      array_keys($conditions)
    ));

    // SQL query
    $query = !empty(trim($conditions_)) ? // conditions is empty
      "DELETE FROM $table WHERE $conditions_" :
      "DELETE FROM $table"
    ;

    try {
      $stmt = $conn -> prepare($query);
      return $stmt -> execute($conditions);
    } catch (Exception $e) {
      throw new DBException("SQL query: $query" . $e -> getMessage());
    }
  }


}