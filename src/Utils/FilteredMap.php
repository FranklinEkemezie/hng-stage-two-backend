<?php

namespace App\Utils;

use App\Exception\NotFoundException;

class FilteredMap {

  public function __construct(
    private array $map
  )
  {
    
  }

  /**
   * Checks if a the map contains a key
   * 
   * @param string $name The name of the key
   * @return bool Returns TRUE if the key is found, otherwise, FALSE
   */
  public function has(string $name): bool {
    return isset($this -> map[$name]);
  }

  /**
   * Gets a map item
   * @param string $name The name of the key to get
   * @return mixed The value of the map item if found, else throws an error
   */
  public function get(string $name) {
    if($this -> has($name)) {
      return $this -> map[$name];
    }
    throw new NotFoundException("Key $name not found in the map");
  }

  /**
   * Gets a map item as an integer value
   * @param  string $name The name of the key to get
   * @return int Returns an integer value with the given key if found, 
   * else throws an error
   */
  public function getInt(string $name): int {
    return (int) $this -> get($name);
  }

  /**
   * Gets a map item as a string value
   * @param string $name The name of the key to get
   * @param bool $filter Specifies whether to filter the string or not.
   * Default is TRUE, which filters the string
   * @return string Returns a string value with the given key if found,
   * else throws an error
   */
  public function getString(string $name, bool $filter = TRUE): string {
    $value = (string) $this -> get($name);
    return $filter ? addslashes($value) : $value;
  }





  
}