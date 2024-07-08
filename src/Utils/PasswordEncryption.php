<?php

namespace App\Utils;

class PasswordEncryption
{
  // Cost factor for bcrypt algorithm (higher is slower but more secure)
  private const COST = 12;

  /**
   * Hashes a password using bcrypt.
   *
   * @param string $password The password to hash.
   * @return string The hashed password.
   */
  public static function hashPassword(string $password): string
  {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => self::COST]);
  }

  /**
   * Verifies if a password matches its hash.
   *
   * @param string $password The password to verify.
   * @param string $hashedPassword The hashed password stored in the database.
   * @return bool True if the password matches the hash, false otherwise.
   */
  public static function verifyPassword(string $password, string $hashedPassword): bool
  {
    return password_verify($password, $hashedPassword);
  }

  /**
   * Optional: Generates a salt (random bytes) for additional security.
   *
   * @param int $length Length of the salt in bytes.
   * @return string The generated salt.
   */
  public static function generateSalt(int $length = 16): string
  {
    return random_bytes($length);
  }
}
