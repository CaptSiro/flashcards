<?php
  
  use OakBase\Database;
  use OakBase\MixedIndexingException;
  use OakBase\PrimitiveParam;
  
  class User {
    public string $email;
    public int $id;
  
  
  
    static function get_by_id (int $id): Result {
      $id = new PrimitiveParam($id);
    
      return success(Database::get()->fetch(
        "SELECT id, email FROM users WHERE id = $id",
        self::class
      ));
    }
    
    
    
    static function get_by_email (string $email): Result {
      $email = new PrimitiveParam($email);
  
      return success(Database::get()->fetch(
        "SELECT id, email FROM users WHERE email = $email",
        self::class
      ));
    }
    
    
    
    static function insert (string $email): Result {
      $email = new PrimitiveParam($email);
  
      return success(Database::get()->statement(
        "INSERT INTO users (email) VALUE ($email)"
      ));
    }
  }