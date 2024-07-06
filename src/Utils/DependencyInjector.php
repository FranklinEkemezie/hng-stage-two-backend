<?php

namespace App\Utils;
use App\Exception\NotFoundException;

class DependencyInjector {
  private array $dependencies = [];

  /**
   * Sets a dependency injector
   * @param string $name The name of the dependency
   * @param object $object The dependency to add
   */
  public function set(string $name, object $dependency) {
    $this -> dependencies[$name] = $dependency;
  }

  /**
   * Gets a dependency injector
   * @param string $name The name of the dependency
   * @return object Returns the dependency object instance
   * @throws NotFoundException when the dependency with the key `name` is not found
   */
  public function get(string $name): object {
    if(isset($this -> dependencies[$name])) return $this -> dependencies[$name];
    throw new NotFoundException("Error getting dependency: Dependency $name not found");
  }
}