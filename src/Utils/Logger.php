<?php

namespace App\Utils;

use App\Exception\NotFoundException;

/**
 * Logger Class
 * 
 * Handles logging of application events,
 * warning and errors
 * 
 * @method void debug(string $message) Logs a message to DEBUG level
 * @method void info(string $message) Logs a message to INFO level
 * @method void notice(string $message) Logs a message to NOTICE level
 * @method void warning(string $message) Logs a message to WARNING level
 * @method void error(string $message) Logs a message to ERROR level
 * @method void critical(string $message) Logs a message to CRITICAL level
 * @method void alert(string $message) Logs a message to ALERT level
 * @method void emergency(string $message) Logs a message to EMERGENCY level
 * 
 * @property ?string $logFilename The path to the log file
 * @property string $name The name of the logger
 */
class Logger {
  // Class properties
  private ?string $logFilename = NULL;

  // Logger Levels
  private const DEBUG_LEVEL = 120;
  private const INFO_LEVEL = 130;
  private const NOTICE_LEVEL = 140;
  private const WARNING_LEVEL = 150;
  private const ERROR_LEVEL = 160;
  private const CRITICAL_LEVEL = 170;
  private const ALERT_LEVEL = 180;
  private const EMERGENCY_LEVEL = 190;

  private const LEVEL = array(
    120 => 'DEBUG',
    130 => 'INFO',
    140 => 'NOTICE',
    150 => 'WARNING',
    160 => 'ERROR',
    170 => 'CRITICAL',
    180 => 'ALERT',
    190 => 'EMERGENCY'
  );

  public function __construct(
    private string $name
  ) {

  }

  private function addRecord(
    int $level,
    string $message
  ) {
    if(!isset($this -> logFilename) || empty($this -> logFilename)) {
      throw new \Exception("Log file is not set.");
    }

    if(file_exists($this -> logFilename)) {
      $file = fopen($this -> logFilename, "a") or die("Unable to open file: " . $this -> logFilename);
      $log_msg = $this -> getLogMessage($level, $message);
  
      fwrite($file, $log_msg);
  
      fclose($file);
    } else {
      throw new NotFoundException("Log file" . $this -> logFilename .  " not found");
    }

  }

  /**
   * Constructs the log message for a log
   * 
   * @param int $level The Logger level
   * @param string $message The message to log
   * 
   * @return string Returns the log message with the given criteria
   * 
   */
  private function getLogMessage(int $level, string $message): string {
    $date = date("D M j G:i:s Y");
    $level_name = self::getLevelName($level);
    $logger_name = $this -> name;

    $log_msg = "[$date] $logger_name.$level_name $message";
    $log_msg = preg_replace("/\s+/", " ", $log_msg);

    return $log_msg . "\n";
  }

  /**
   * Get the name of a Logger level with the Logger level ID
   * 
   * @param int $level A valid integer representing the Logger Level
   * 
   * @return string Retunrs the name of the Logger level
   */
  private static function getLevelName(int $level): string {
    if(isset(self::LEVEL[$level])) {
      return self::LEVEL[$level];
    }

    throw new NotFoundException("Level ID $level not found!");
  }

  /**
   * Gets the name of the logger
   * 
   * @return string Returns the name of the logger
   */
  public function getName(): string {
    return $this -> name;
  }

  /**
   * Sets the log file for the logger to use
   */
  public function setLogFile(string $log_filename) {
    $this -> logFilename = $log_filename;
  }

  /**
   * Gets the name of the log file
   * 
   * @return string Returns the file name of the log file. Returns NULL if empty
   */
  public function getLogFilename(): ?string {
    return $this -> logFilename;
  }


  /**
   * Logs a message to DEBUG level
   */
  public function debug(string $message) {
    $this -> addRecord(self::DEBUG_LEVEL, $message);
  }

  /**
   * Logs a message to INFO level
   */
  public function info(string $message) {
    $this -> addRecord(self::INFO_LEVEL, $message);
  }

  /**
   * Logs a message to NOTICE level
   */
  public function notice(string $message) {
    $this -> addRecord(self::NOTICE_LEVEL, $message);
  }

  /**
   * Logs a message to WARNING level
   */
  public function warning(string $message) {
    $this -> addRecord(self::WARNING_LEVEL, $message);
  }

  /**
   * Logs a message to ERROR level
   */
  public function error(string $message) {
    $this -> addRecord(self::ERROR_LEVEL, $message);
  }

  /**
   * Logs a message to CRITICAL level
   */
  public function critical(string $message) {
    $this -> addRecord(self::CRITICAL_LEVEL, $message);
  }

  /**
   * Logs a message to ALERT level
   */
  public function alert(string $message) {
    $this -> addRecord(self::ALERT_LEVEL, $message);
  }

  /**
   * Logs a message to EMERGENCY level
   */
  public function emergency(string $message) {
    $this -> addRecord(self::EMERGENCY_LEVEL, $message);
  }

}