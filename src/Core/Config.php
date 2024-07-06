<?php

namespace App\Core;

use App\Exception\NotFoundException;

class Config {
  private mixed $data;
  private static ?Config $instance = NULL;

  /**
   * Class constructor
   * @private
   */
  private function __construct(string $configFilename) {
    if(file_exists($configFilename)) {
      $configJson = file_get_contents($configFilename);
      $this -> data = json_decode($configJson, TRUE);
    } else {
      throw new NotFoundException("Error loading app configurations: Config file $configFilename not found");
    }
  }

  /**
   * Returns an instance of the app configuration
   * 
   * @param string $$configFilename The file path of the configuration file
   * @return Config Returns the configuration instance
   */
  public static function getInstance(string $configFilename): Config {
    if(self::$instance === NULL) self::$instance = new Config($configFilename);
    return self::$instance;
  }

  /**
   * Gets a configuraiton setting
   * @param string $key The name or key of the configuration setting
   * @return mixed Returns the (value of) the specified configuration setting
   */
  public function get($key) {
    if(isset($this -> data[$key])) return $this -> data[$key];
    throw new NotFoundException("Config key `$key` not found!");
  }
}