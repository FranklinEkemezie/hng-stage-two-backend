<?php

namespace App\Domain;

/**
 * Organisation class
 * 
 * Models an organisation created by a user
 */
class Organisation {

  public function __construct(
    private string $orgId, // unique
    private string $name, // creatorFirstName's organisation
    private string $description="",
    private string $createdBy 
  ) {

  }

  public function getOrgId(): string {
    return $this -> orgId;
  }

  public function getName(): string {
    return $this -> name;
  }

  public function getDescription(): string {
    return $this -> description;
  }

  public function createdBy(): string {
    return $this -> createdBy;
  }


}