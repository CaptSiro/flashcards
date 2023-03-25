<?php
  
  use OakBase\Database;
  use OakBase\Param;
  use OakBase\PrimitiveParam;
  
  class User {
    public string $email;
    public int $id;
  
  
  
    static function by_id (Param $id): Result {
      return success(Database::get()->fetch(
        "SELECT id, email FROM users WHERE id = $id",
        self::class
      ));
    }
    
    
    
    static function by_email (Param $email): Result {
      return success(Database::get()->fetch(
        "SELECT id, email FROM users WHERE email = $email",
        self::class
      ));
    }
    
    
    
    static function insert (Param $email): Result {
      return success(Database::get()->statement(
        "INSERT INTO users (email) VALUE ($email)"
      ));
    }
  }